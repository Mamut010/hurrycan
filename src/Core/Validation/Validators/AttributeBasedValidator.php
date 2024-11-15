<?php
namespace App\Core\Validation\Validators;

use App\Core\Shared\Computed;
use App\Core\Shared\LateComputed;
use App\Core\Validation\Attributes\FailFast;
use App\Core\Validation\Attributes\RequiredMessage;
use App\Core\Validation\Attributes\ValidateNested;
use App\Core\Validation\Bases\IsOptionalBase;
use App\Core\Validation\Contracts\PropertyValidator;
use App\Core\Validation\Contracts\Validator;
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

            $execution = new AttributeBasedValidatingExecution($this, $subject);
            return $execution->validate($validationModel);
        }
        catch (\ReflectionException $e) {
            throw new \InvalidArgumentException("Invalid validation model [$validationModel]", 0, $e);
        }
    }
}

class AttributeBasedValidatingExecution
{
    private bool $failFastModel = false;

    /**
     * @param Validator $validator
     * @param array<string,mixed> $subject
     */
    public function __construct(
        private readonly Validator $validator,
        private readonly array $subject) {
        
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
        $isOptional = Reflections::getAttribute($prop, IsOptionalBase::class, \ReflectionAttribute::IS_INSTANCEOF);
        if (!$isOptional) {
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
        $validateNested = Reflections::getAttribute($prop, ValidateNested::class);
        $result = null;
        if ($validateNested) {
            $result = $this->handleValidateNested($prop, $outputValue);
        }
        
        // If not handled
        if ($result === null) {
            return $this->invokePropertyValidators($prop, $outputValue);
        }
        // If handled and there are errors
        elseif ($result instanceof ValidationErrorBag) {
            return $result;
        }
        // Handled and no error
        else {
            return null;
        }
    }

    private function handleValidateNested(\ReflectionProperty $prop, mixed &$outputValue) {
        $propName = $prop->getName();
        $type = $prop->getType();

        // Skip untyped property
        if (!$type) {
            return null;
        }

        if (!$type instanceof \ReflectionNamedType) {
            $typeMsg = $type instanceof \ReflectionIntersectionType ? 'intersection type' : 'union type';
            throw new \ReflectionException("Unsupported validate nested $typeMsg [$type] of property [$propName]");
        }

        // Skip built-in type
        if ($type->isBuiltin()) {
            return null;
        }
        
        $typeName = $type->getName();
        $class = new \ReflectionClass($typeName);
        if (!$class->isInstantiable()) {
            throw new \ReflectionException("Type [$typeName] is non-instantiable");
        }

        $nestedValidationResult = $this->validator->validate($this->subject[$propName], $typeName);
        // If no error
        if (!$nestedValidationResult instanceof ValidationErrorBag) {
            $outputValue = $nestedValidationResult;
        }
        return $nestedValidationResult instanceof ValidationErrorBag ? $nestedValidationResult : true;
    }

    private function invokePropertyValidators(\ReflectionProperty $prop, mixed &$outputValue) {
        $validatorAttributes = $prop->getAttributes(PropertyValidator::class, \ReflectionAttribute::IS_INSTANCEOF);
        $propName = $prop->getName();
        foreach ($validatorAttributes as $validatorAttribute) {
            $validator = $validatorAttribute->newInstance();
            $errorMessage = $validator->validate($prop, $this->subject, $this->subject[$propName]);
            if ($errorMessage !== null) {
                return $errorMessage;
            }
        }
        $outputValue = $this->subject[$propName];
        return null;
    }
}
