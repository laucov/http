<?php

/**
 * This file is part of Laucov's HTTP Library project.
 * 
 * Copyright 2024 Laucov Serviços de Tecnologia da Informação Ltda.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * @package http
 * 
 * @author Rafael Covaleski Pereira <rafael.covaleski@laucov.com>
 * 
 * @license <http://www.apache.org/licenses/LICENSE-2.0> Apache License 2.0
 * 
 * @copyright © 2024 Laucov Serviços de Tecnologia da Informação Ltda.
 */

namespace Laucov\Http\Routing\Traits;

use Laucov\Http\Message\RequestInterface;
use Laucov\Http\Message\ResponseInterface;
use Laucov\Http\Routing\RouteClosureType;

/**
 * Has properties and methods to analyze route callables.
 */
trait RouteCallableTrait
{
    /**
     * Allowed closure parameter types.
     */
    public const ALLOWED_PARAMETER_TYPES = [
        'string',
        RequestInterface::class,
    ];

    /**
     * Allowed closure return types.
     */
    public const ALLOWED_RETURN_TYPES = [
        'string',
        \Stringable::class,
        ResponseInterface::class,
    ];

    /**
     * Registered closure.
     */
    public \Closure $closure;

    /**
     * Closure parameter types.
     * 
     * @var array<RouteClosureType>
     */
    public array $parameterTypes;

    /**
     * Closure return type.
     */
    public string $returnType;

    /**
     * Validate and register the callable's parameter and return types.
     */
    protected function validate(\ReflectionFunctionAbstract $reflection): void
    {
        $this->validateParameterTypes($reflection);
        $this->validateReturnType($reflection);
    }

    /**
     * Find the closure's return type name.
     */
    protected function validateReturnType(
        \ReflectionFunctionAbstract $reflection,
    ): void {
        // Get the ReflectionType object and check if is a named type.
        $return_type = $reflection->getReturnType();
        if (!($return_type instanceof \ReflectionNamedType)) {
            $message = 'Route callables must not return union or '
                    . 'intersection types.';
            throw new \InvalidArgumentException($message);
        }

        // Get the return type name.
        $name = $return_type->getName();
        if (!in_array($name, static::ALLOWED_RETURN_TYPES)) {
            $allowed = implode(', ', static::ALLOWED_RETURN_TYPES);
            $message = 'Route callables must only return one of the ' .
                'following types: %s. Got %s.';
            $message = sprintf($message, $allowed, $name);
            throw new \InvalidArgumentException($message);
        }

        $this->returnType = $name;
    }

    /**
     * Find the closure's parameter type names.
     */
    protected function validateParameterTypes(
        \ReflectionFunctionAbstract $reflection,
    ): void {
        // Initialize type array.
        $types = [];
        
        // Get and validate parameters.
        $parameters = $reflection->getParameters();
        foreach ($parameters as $parameter) {
            // Check if is not union or intersection type.
            $type = $parameter->getType();
            if (!($type instanceof \ReflectionNamedType)) {
                $message = 'Route callables cannot receive union or '
                    . 'intersection parameter types.';
                throw new \InvalidArgumentException($message);
            }
            // Get and check type name.
            $name = $type->getName();
            if (!in_array($name, static::ALLOWED_PARAMETER_TYPES)) {
                $allowed = implode(', ', static::ALLOWED_PARAMETER_TYPES);
                $message = 'Route callables must only receive parameteres ' .
                    'of the following types: %s. Got %s.';
                $message = sprintf($message, $allowed, $name);
                throw new \InvalidArgumentException($message);
            }
            // Add type to array.
            $result = new RouteClosureType();
            $result->name = $name;
            $result->isVariadic = $parameter->isVariadic();
            $types[] = $result;
        }

        $this->parameterTypes = $types;
    }
}
