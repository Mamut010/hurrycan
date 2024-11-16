<?php
namespace App\Core\Validation\Validators;

use App\Core\Shared\Computed;
use App\Core\Shared\LateComputed;
use App\Core\Validation\Attributes\FailFast;
use App\Core\Validation\Attributes\IsRequired;
use App\Core\Validation\Attributes\RequiredMessage;
use App\Core\Validation\Attributes\ValidateNested;
use App\Core\Validation\Bases\IsOptionalBase;
use App\Core\Validation\Contracts\PropertyValidator;
use App\Core\Validation\Contracts\Validator;
use App\Core\Validation\ValidationContext;
use App\Core\Validation\ValidationErrorBag;
use App\Utils\Converters;
use App\Utils\Reflections;

class AttributeBasedValidator implements Validator
{
    #[\Override]
    public function validate(array|object $subject, string $validationModel): object {
        $modelInstance = Reflections::instantiateClass($validationModel);
        if (!$modelInstance) {
            $reason = "validation model must be a class having a no-argument constructor";
            throw new \InvalidArgumentException("Invalid validation model [$validationModel]: $reason");
        }

        try {
            if (!is_array($subject)) {
                $subject = Converters::objectToArray($subject);
            }

            $execution = new AttributeBasedValidatingExecution($this, $subject, $modelInstance);
            return $execution->validate($validationModel);
        }
        catch (\ReflectionException $e) {
            throw new \InvalidArgumentException("Invalid validation model [$validationModel]", 0, $e);
        }
    }
}

/**
 * @template T of object
 */
class AttributeBasedValidatingExecution
{
    private bool $failFastModel = false;
    private ?IsOptionalBase $optionalModel = null;

    /**
     * @param Validator $validator
     * @param array<string,mixed> $subject
     * @param T $modelInstance
     */
    public function __construct(
        private readonly Validator $validator,
        private readonly array $subject,
        private readonly object $modelInstance) {
        
    }

    /**
     * @param class-string<T> $validationModel
     * @return T|ValidationErrorBag
     */
    public function validate(string $validationModel): object {
        $reflector = new \ReflectionClass($validationModel);
        $this->checkClassAttributes($reflector);

        $errorBag = new ValidationErrorBag();
        $passedProps = [];
        $computeds = [];
        $lateComputeds = [];
        $props = $reflector->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach ($props as $prop) {
            $propName = $prop->getName();
            $failFast = $this->failFastModel || (Reflections::getAttribute($prop, FailFast::class) !== false);

            $this->handleLateComputedAttribute($prop, $lateComputeds);

            if (!array_key_exists($propName, $this->subject)) {
                $passed = $this->handleMissingProp($reflector, $prop, $errorBag, $passedProps);
                if (!$passed && $failFast) {
                    break;
                }
                continue;
            }
            
            $this->handleComputedAttribute($prop, $computeds);

            $error = $this->validateProp($prop, $outputValue);
            if ($error === null) {
                $passedProps[$propName] = $outputValue;
                continue;
            }

            $errorBag->add($propName, $error);
            if ($failFast) {
                break;
            }
        }

        if (!$errorBag->isEmpty()) {
            return $errorBag;
        }

        $instance = Converters::arrayToObject($passedProps, $validationModel, propSetters: $computeds);
        if (!$instance) {
            throw new \ReflectionException("Unable to instantiate validation model [$validationModel]");
        }

        foreach ($lateComputeds as $propName => $callback) {
            $instance->{$propName} = call_user_func($callback, $instance);
        }
        return $instance;
    }

    private function checkClassAttributes(\ReflectionClass $class) {
        $this->failFastModel = Reflections::getAttribute($class, FailFast::class) !== false;
        $this->optionalModel = Reflections::getAttribute(
            $class,
            IsOptionalBase::class,
            \ReflectionAttribute::IS_INSTANCEOF
        ) ?: null;
    }

    private function handleLateComputedAttribute(\ReflectionProperty $prop, array &$lateComputeds) {
        $propName = $prop->getName();
        $lateComputedAttribute = Reflections::getAttribute($prop, LateComputed::class);
        if ($lateComputedAttribute) {
            $lateComputeds[$propName] = fn(object $instance) => $lateComputedAttribute->compute($instance, $prop);
        }
    }

    private function handleMissingProp(
        \ReflectionClass $class,
        \ReflectionProperty $prop,
        ValidationErrorBag $errorBag,
        array &$passedProps,
    ) {
        $propName = $prop->getName();
        $isOptional = $this->optionalModel;
        if (!$isOptional) {
            $isOptional = Reflections::getAttribute($prop, IsOptionalBase::class, \ReflectionAttribute::IS_INSTANCEOF);
        }

        $isRequiredProp = Reflections::getAttribute($prop, IsRequired::class) !== false;

        if ($isRequiredProp || !$isOptional) {
            $requiredMessageAttribute = static::getRequiredMessageAttribute($class, $prop);
            $requiredErrorMessage = $requiredMessageAttribute->getMessage($propName);
            $errorBag->add($propName, $requiredErrorMessage);
            return false;
        }

        $defaultValue = $isOptional->getDefaultValue();
        if ($defaultValue->isPresent()) {
            $passedProps[$propName] = $defaultValue->get();
        }
        return true;
    }

    private static function getRequiredMessageAttribute(\ReflectionClass $class, \ReflectionProperty $prop) {
        $requiredMessageAttribute = Reflections::getAttribute($prop, RequiredMessage::class);
        $requiredMessageAttribute = $requiredMessageAttribute ?: Reflections::getAttribute($class, RequiredMessage::class);
        return $requiredMessageAttribute ?: new RequiredMessage("Field '{property}' is required");
    }

    private function handleComputedAttribute(\ReflectionProperty $prop, array &$computeds) {
        $propName = $prop->getName();
        $computedAttribute = Reflections::getAttribute($prop, Computed::class);
        if ($computedAttribute) {
            $computeds[$propName] = fn(object $instance, mixed $value, \ReflectionProperty $prop)
                => $computedAttribute->compute($instance, $value, $prop);
        }
    }

    private function validateProp(\ReflectionProperty $prop, mixed &$outputValue) {
        return $this->invokePropertyValidators($prop, $outputValue);
    }

    private function invokePropertyValidators(\ReflectionProperty $prop, mixed &$outputValue) {
        $validatorAttributes = $prop->getAttributes(PropertyValidator::class, \ReflectionAttribute::IS_INSTANCEOF);
        $propName = $prop->getName();
        $context = new ValidationContext($this->validator, $this->modelInstance, $this->subject, $propName);
        $isOutputSet = false;
        foreach ($validatorAttributes as $validatorAttribute) {
            $validator = $validatorAttribute->newInstance();
            $validationResult = $validator->validate($context);
            if ($validationResult->isFailure()) {
                return $validationResult->getError();
            }
            elseif ($validationResult->containsSuccessfulValue()) {
                $outputValue = $validationResult->getResult();
                $isOutputSet = true;
            }
        }
        
        if (!$isOutputSet) {
            $outputValue = $this->subject[$propName];
        }
        return null;
    }
}
