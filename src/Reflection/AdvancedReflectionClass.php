<?php

namespace Niki9796dk\LaravelDataTransferRequests\Reflection;

use ReflectionClass;
use ReflectionProperty;
use ReflectionException;
use BadMethodCallException;
use Illuminate\Support\Collection;

/**
 * @mixin ReflectionClass
 */
class AdvancedReflectionClass
{
    private ReflectionClass $reflectionClass;

    /**
     * Constructor
     *
     * @param object|class-string $classOrInstance
     *
     * @throws ReflectionException
     */
    public function __construct(object|string $classOrInstance)
    {
        $this->reflectionClass = new ReflectionClass($classOrInstance);
    }

    /**
     * Returns all public properties
     *
     * @return Collection<int, ReflectionProperty>
     */
    public function getAllPublicProperties(): Collection
    {
        return collect($this->reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC));
    }

    /**
     * Magic call for implementing mixin
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        if (method_exists($this->reflectionClass, $name)) {
            return $this->reflectionClass->$name(...$arguments);
        }

        throw new BadMethodCallException('No such method : ' . $name);
    }
}
