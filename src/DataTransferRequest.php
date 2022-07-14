<?php

namespace Niki9796dk\LaravelDataTransferRequests;

use TypeError;
use ReflectionType;
use ReflectionProperty;
use ReflectionNamedType;
use ReflectionUnionType;
use ReflectionAttribute;
use BadMethodCallException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidatesWhenResolvedTrait;
use Niki9796dk\LaravelDataTransferRequests\RuleSets\RuleSet;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Niki9796dk\LaravelDataTransferRequests\Attributes\AddsRules;
use Niki9796dk\LaravelDataTransferRequests\Reflection\AdvancedReflectionClass;

/**
 * @implements Arrayable<string, mixed>
 */
abstract class DataTransferRequest implements Arrayable
{
    /**
     * We use the trait, but do not implement the contract.
     * This is because we want to have the default implementation (drop-in-replacement)
     * but do not want any of the auto validate when resolved logic.
     */
    use ValidatesWhenResolvedTrait;

    /**
     * Original Data given to the object
     *
     * @var array
     */
    private array $original;

    /**
     * A reflection instance of this object
     *
     * @var AdvancedReflectionClass
     */
    private AdvancedReflectionClass $reflection;

    /**
     * The compiled validation rule set
     *
     * @var RuleSet
     */
    private RuleSet $ruleSet;

    /**
     * The validator instance
     *
     * @var Validator
     */
    private Validator $validator;

    /**
     * Indicates whether validation should stop after the first rule failure.
     *
     * @var bool
     */
    protected bool $stopOnFirstFailure = false;

    /**
     * Constructor
     *
     * @param array|null $data
     *
     * @throws BindingResolutionException
     */
    public function __construct(?array $data = null)
    {
        $this->reflection = resolve(AdvancedReflectionClass::class, ['classOrInstance' => $this]);

        if ($data !== null) {
            $this->setData($data);
        }
    }

    /**
     * Sets the data for the object
     *
     * @param array $data
     *
     * @throws BindingResolutionException
     *
     * @return $this
     */
    public function setData(array $data): static
    {
        if (isset($this->original)) {
            throw new BadMethodCallException('Cannot set data multiple times');
        }

        $this->original = $data;

        // First run the validator on the original data
        $this->validateResolved();

        // Then populate the DTO, while converting any type errors to validation errors.
        try {
            $this->populateProperties();
        } catch (TypeError $typeError) {
            $this->failTypeSafetyValidation($typeError);
        }

        return $this;
    }

    /**
     * Populates the class attributes
     *
     * @return void
     */
    private function populateProperties(): void
    {
        foreach ($this->reflection->getAllPublicProperties() as $property) {
            $this->populateProperty($property, $this->getOriginalPropertyValue($property));
        }
    }

    /**
     * Populates a single property
     *
     * @param ReflectionProperty $property
     * @param mixed $value
     *
     * @return void
     */
    private function populateProperty(ReflectionProperty $property, mixed $value): void
    {
        $propertyType = $property->getType();

        if (is_array($value)) {
            if ($propertyType instanceof ReflectionNamedType && is_a($propertyType->getName(), DataTransferRequest::class, true)) {
                $value = new ($propertyType->getName())($value);
            } elseif ($this->acceptsBuiltInObjectProperty($propertyType)) {
                $value = (object) $value;
            }
        }

        $property->setValue($this, $value);
    }

    private function getOriginalPropertyValue(ReflectionProperty $property): mixed
    {
        $propertyType = $property->getType();

        if ($propertyType->allowsNull()) {
            return $this->original[$property->getName()] ?? null;
        }

        return $this->original[$property->getName()];
    }

    /**
     * Returns true if the given type accepts the built-in base object type
     *
     * @param ReflectionType|null $type
     *
     * @return bool
     */
    private function acceptsBuiltInObjectProperty(?ReflectionType $type): bool
    {
        if ($type === null) {
            return false;
        }

        if ($type instanceof ReflectionNamedType) {
            return $type->getName() === 'object';
        }

        if ($type instanceof ReflectionUnionType) {
            return collect($type->getTypes())->first(fn ($subType) => $this->acceptsBuiltInObjectProperty($subType)) ?: false;
        }

        throw new \RuntimeException('Unexpected reflection type : ' . $type::class);
    }

    /**
     * Throws a validation error, because of failed type safety
     *
     * @param TypeError $typeError
     *
     * @throws BindingResolutionException
     *
     * @return void
     */
    private function failTypeSafetyValidation(TypeError $typeError): void
    {
        // Parse the error
        $regex = '/Cannot assign (?<actual>\S+) to property .+::\\$(?<attribute>\w+) of type (?<expected>\S+)/';
        preg_match($regex, $typeError->getMessage(), $matches);

        // If any other error than an assignment type error, then throw it.
        if (empty($matches)) {
            throw $typeError;
        }

        // Add the error to the response message
        $validator = $this->getValidatorInstance();

        $validator->errors()->add($matches['attribute'], sprintf('Expected type [%s] but [%s] was given.', $matches['expected'], $matches['actual']));
        $this->failedValidation($validator);
    }

    /**
     * Get the validator instance for the request.
     *
     * @throws BindingResolutionException
     *
     * @return Validator
     */
    protected function getValidatorInstance(): Validator
    {
        return $this->validator
            ??= $this->createValidatorInstance();
    }

    /**
     * Creates a new validator instance
     *
     * @throws BindingResolutionException
     *
     * @return Validator
     */
    private function createValidatorInstance(): Validator
    {
        $validator = resolve(ValidationFactory::class)->make(
            $this->original, $this->getRuleSet()->toRules(),
            $this->messages(), $this->attributes()
        )->stopOnFirstFailure($this->stopOnFirstFailure);

        if (method_exists($this, 'withValidator')) {
            $this->withValidator($validator);
        }

        return $validator;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    protected function messages(): array
    {
        return [];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    protected function attributes(): array
    {
        return [];
    }

    /**
     * Compiles the DataRequest object and all its dependencies, into a ruleset
     *
     * @return RuleSet
     */
    private function getRuleSet(): RuleSet
    {
        /** @noinspection NullCoalescingOperatorCanBeUsedInspection */
        if (isset($this->ruleSet)) {
            return $this->ruleSet;
        }

        return tap($this->ruleSet = new RuleSet(), function (RuleSet $ruleSet) {
            foreach ($this->reflection->getAllPublicProperties() as $property) {
                $ruleSet->add($property->getName(), $this->reflectionTypeToValidationRule($property->getType()));

                if ($property->getType()->allowsNull()) {
                    $ruleSet->add($property->getName(), 'nullable');
                }

                foreach ($property->getAttributes(AddsRules::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                    $ruleSet->add($property->getName(), $attribute->newInstance()->getRules());
                }
            }
        });
    }

    /**
     * Converts a given Reflection type into an array of validation rules
     *
     * @param ReflectionType|null $type
     *
     * @return array|string|RuleSet
     */
    private function reflectionTypeToValidationRule(ReflectionType|null $type): array|string|RuleSet
    {
        if ($type === null) {
            return [];
        }

        if ($type instanceof ReflectionNamedType) {
            $name = $type->getName();

            if ($type->isBuiltin()) {
                return [
                    [
                        'int'    => 'integer',
                        'string' => 'string',
                        'float'  => 'numeric',
                        'array'  => 'array',
                        'object' => 'array',
                        'mixed'  => 'nullable',
                        'bool'   => 'boolean',
                    ][$name],
                    // TODO: Remove double nullable here
                    ...($type->allowsNull() ? ['nullable'] : ['required']),
                ];
            }

            if (is_a($name, DataTransferRequest::class, true)) {
                // TODO: maybe just make this method static...
                /** @var DataTransferRequest $nestedDataTransferRequest */
                $nestedDataTransferRequest = new $name();

                return $nestedDataTransferRequest->getRuleSet();
            }

            return ['array'];
        }

        if ($type instanceof ReflectionUnionType) {
            // TODO: Change from AND logic to OR logic. (Current is int AND boolean -> but should be it OR boolean)
            return collect($type->getTypes())->flatMap(fn (ReflectionType|null $subType) => $this->reflectionTypeToValidationRule($subType))->toArray();
        }

        return [''];
    }

    /**
     * Get the instance as an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->original;
    }
}
