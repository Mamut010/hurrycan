<?php
namespace App\Core\Validation\Validators;

use App\Core\Shared\Computed;
use App\Core\Shared\LateComputed;
use App\Core\Validation\Attributes\FailFast;
use App\Core\Validation\Attributes\IsRequired;
use App\Core\Validation\Attributes\RequiredMessage;
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
        try {
            if (!is_array($subject)) {
                $subject = Converters::objectToArray($subject);
            }

            $execution = new AttributeBasedValidatingExecution($this, $validationModel, $subject);
            return $execution->run();
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
    private array $passedPropNames;
    private array $errorPropNames;
    private ValidationContext $ctx;

    private bool $failFastModel = false;
    private ?IsOptionalBase $optionalModel = null;

    /**
     * @param Validator $validator The validator used in the current validation context
     * @param class-string<T> $validationModel The validation model to validate against
     * @param array<string,mixed> $subject The subject to validate
     */
    public function __construct(
        Validator $validator,
        private readonly string $validationModel,
        private readonly array $subject
    ) {
        $this->passedPropNames = [];
        $this->errorPropNames = [];
        $this->ctx = new ValidationContext(
            $validator,
            $this->generateDummyModelInstance(), // Dummy instance is used to avoid unintenional changes to the actual instance
            $subject,
            $this->passedPropNames,
            $this->errorPropNames
        );
    }

    /**
     * @return T|ValidationErrorBag
     */
    public function run(): object {
        $class = new \ReflectionClass($this->validationModel);
        $this->checkClassAttributes($class);

        [$passedProps, $errorBag, $computeds, $lateComputeds] = $this->validateModelProperties($class);

        if (!$errorBag->isEmpty()) {
            return $errorBag;
        }

        $instance = Converters::arrayToObject($passedProps, $this->validationModel, propSetters: $computeds);
        if (!$instance) {
            throw new \ReflectionException("Unable to instantiate validation model [$this->validationModel]");
        }

        foreach ($lateComputeds as $propName => $callback) {
            $instance->{$propName} = call_user_func($callback, $instance);
        }
        return $instance;
    }

    private function generateDummyModelInstance() {
        $modelInstance = Reflections::instantiateClass($this->validationModel);
        if (!$modelInstance) {
            $reason = "validation model must be a class having a no-argument constructor";
            throw new \InvalidArgumentException("Invalid validation model [$this->validationModel]: $reason");
        }
        return $modelInstance;
    }

    private function checkClassAttributes(\ReflectionClass $class) {
        $this->failFastModel = Reflections::getAttribute($class, FailFast::class) !== false;
        $this->optionalModel = Reflections::getAttribute(
            $class,
            IsOptionalBase::class,
            \ReflectionAttribute::IS_INSTANCEOF
        ) ?: null;
    }

    private function validateModelProperties(\ReflectionClass $class) {
        $errorBag = new ValidationErrorBag();
        /**
         * @var array<string,mixed>
         */
        $passedProps = [];
        /**
         * @var array<string,callable>
         */
        $computeds = [];
        /**
         * @var array<string,callable>
         */
        $lateComputeds = [];

        $props = $class->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach ($props as $prop) {
            $propName = $prop->getName();
            $failFast = $this->failFastModel || (Reflections::getAttribute($prop, FailFast::class) !== false);

            if ($this->handleLateComputedAttribute($prop, $lateComputeds)) {
                continue;
            }

            if (!array_key_exists($propName, $this->subject)) {
                $passed = $this->handleMissingProp($class, $prop, $errorBag, $passedProps);
                $this->updateValidationContext($propName, $passed);
                if (!$passed && $failFast) {
                    break;
                }
                continue;
            }
            
            $this->handleComputedAttribute($prop, $computeds);

            $error = $this->invokePropertyValidators($prop, $outputValue);
            $passed = $error === null;
            $this->updateValidationContext($propName, $passed);
            if ($passed) {
                $passedProps[$propName] = $outputValue;
                continue;
            }

            $errorBag->add($propName, $error);
            if ($failFast) {
                break;
            }
        }

        return [$passedProps, $errorBag, $computeds, $lateComputeds];
    }

    private function handleLateComputedAttribute(\ReflectionProperty $prop, array &$lateComputeds) {
        $propName = $prop->getName();
        $lateComputedAttribute = Reflections::getAttribute($prop, LateComputed::class);
        if (!$lateComputedAttribute) {
            return false;
        }

        $lateComputeds[$propName] = fn(object $instance) => $lateComputedAttribute->compute($instance, $prop);
        return true;
    }

    private function updateValidationContext(string $propName, bool $passed) {
        if ($passed) {
            $this->passedPropNames[$propName] = $passed;
        }
        else {
            $this->errorPropNames[$propName] = $passed;
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

    private function invokePropertyValidators(\ReflectionProperty $prop, mixed &$outputValue) {
        $validatorAttributes = $prop->getAttributes(PropertyValidator::class, \ReflectionAttribute::IS_INSTANCEOF);
        $propName = $prop->getName();
        
        $isOutputset = false;
        foreach ($validatorAttributes as $validatorAttribute) {
            $validator = $validatorAttribute->newInstance();
            $validationResult = $validator->validate($this->ctx, $propName);
            if ($validationResult->isFailure()) {
                return $validationResult->getError();
            }
            elseif ($validationResult->containsValue()) {
                $outputValue = $validationResult->getValue();
                $isOutputset = true;
            }
        }
        
        if (!$isOutputset) {
            $outputValue = $this->subject[$propName];
        }
        return null;
    }
}
