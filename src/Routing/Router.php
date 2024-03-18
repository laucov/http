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

namespace Laucov\Http\Routing;

use Laucov\Arrays\ArrayBuilder;
use Laucov\Http\Message\RequestInterface;
use Laucov\Http\Message\ResponseInterface;
use Laucov\Http\Routing\Call\Callback;
use Laucov\Http\Routing\Call\Interfaces\PreludeInterface;
use Laucov\Http\Routing\Call\Interfaces\RouteInterface;
use Laucov\Http\Routing\Call\Route;
use Laucov\Http\Server\ServerInfo;
use Laucov\Injection\Repository;
use Laucov\Injection\Resolver;
use Laucov\Injection\Validator;

/**
 * Stores routes and assign them to HTTP requests.
 */
class Router
{
    /**
     * Allowed callback return types.
     */
    public const ALLOWED_RETURN_TYPES = [
        'string',
        \Stringable::class,
        ResponseInterface::class,
    ];

    /**
     * Active preludes.
     * 
     * @var array<string>
     */
    protected array $activePreludes = [];

    /**
     * Dependency repository.
     */
    protected Repository $dependencies;

    /**
     * Stored patterns.
     * 
     * @var array<string, string>
     */
    protected array $patterns = [];

    /**
     * Active prefixes.
     * 
     * @var array<string>
     */
    protected array $prefixes = [];

    /**
     * Registered preludes.
     * 
     * @var array<string, array{class-string<PreludeInterface>, array}>
     */
    protected array $preludes = [];

    /**
     * Dependency resolver.
     */
    protected Resolver $resolver;

    /**
     * Stored routes.
     */
    protected ArrayBuilder $routes;

    /**
     * Dependency validator.
     */
    protected Validator $validator;

    /**
     * Create the router instance.
     */
    public function __construct()
    {
        // Setup dependency injection.
        $this->dependencies = new Repository();
        $this->resolver = new Resolver($this->dependencies);
        $this->validator = new Validator($this->dependencies);
        $this->validator->allow(RequestInterface::class);
        $this->validator->allow(ServerInfo::class);
        $this->validator->allow('string');

        // Create route array.
        $this->routes = new ArrayBuilder([]);
    }

    /**
     * Add a new prelude option.
     */
    public function addPrelude(
        string $name,
        string $class_name,
        array $parameters,
    ): static {
        $this->preludes[$name] = [$class_name, $parameters];
        return $this;
    }

    /**
     * Find a route for the given request object.
     */
    public function findRoute(
        RequestInterface $request,
        null|ServerInfo $server = null,
    ): null|RouteInterface {
        // Get method and routes.
        $method = $request->getMethod();
        $routes = (array) $this->routes->getValue($method, []);

        // Get path segments.
        $path = $request->getUri()->path;
        $segments = $path ? explode('/', $path) : [];
        $segments[] = '/';

        // Try to find a route.
        $result = $routes;
        $captured_segments = [];
        foreach ($segments as $segment) {
            // Check for direct match.
            if (array_key_exists($segment, $result)) {
                $result = $result[$segment];
                continue;
            }
            // Check for pattern match.
            foreach ($this->patterns as $name => $pattern) {
                // Ignore unused pattern.
                $key = ':' . $name;
                if (!array_key_exists($key, $result)) {
                    continue;
                }
                // Test the pattern and capture segment.
                if (preg_match($pattern, $segment) === 1) {
                    $result = $result[$key];
                    $captured_segments[] = $segment;
                    continue 2;
                }
            }
            // Route does not exist.
            return null;
        }

        // Check if is a route closure.
        $callback = $result;
        if (!($callback instanceof Callback)) {
            // @codeCoverageIgnoreStart
            $message = 'Found an unexpected %s stored as a route callable.';
            throw new \RuntimeException(sprintf($message, gettype($result)));
            // @codeCoverageIgnoreEnd
        }

        // Set temporary dependencies.
        $this->dependencies->setValue(RequestInterface::class, $request);
        if ($server !== null) {
            $this->dependencies->setValue(ServerInfo::class, $server);
        }

        // Create preludes.
        $preludes = [];
        foreach ($callback->preludeNames as $prelude) {
            // Get prelude data.
            [$class_name, $parameters] = $this->preludes[$prelude];
            // Add dependency and instantiate.
            $this->dependencies->setValue('array', $parameters);
            $preludes[] = $this->resolver->instantiate($class_name);
            $this->dependencies->removeDependency('array');
        }

        // Get parameters.
        $this->dependencies->setIterable('string', $captured_segments);
        $parameters = $this->resolver->resolve($callback->callback);

        // Remove temporary dependencies.
        $this->dependencies->removeDependency(RequestInterface::class);
        $this->dependencies->removeDependency(ServerInfo::class);
        $this->dependencies->removeDependency('string');

        return new Route($callback, $parameters, $preludes);
    }

    /**
     * Remove the last pushed prefix.
     */
    public function popPrefix(): static
    {
        array_pop($this->prefixes);
        return $this;
    }

    /**
     * Prefix the next routes with the given path.
     * 
     * Currently active prefixes will be added before the new prefix.
     */
    public function pushPrefix(string $path): static
    {
        $this->prefixes[] = trim($path, '/');
        return $this;
    }

    /**
     * Set a new pattern for route searches.
     */
    public function setPattern(string $name, string $regex): static
    {
        $this->patterns[$name] = $regex;
        return $this;
    }

    /**
     * Set the current prelude options in use.
     */
    public function setPreludes(string ...$names): static
    {
        $this->activePreludes = $names;
        return $this;
    }

    /**
     * Set a callable (function, instance method, etc.) as a route.
     */
    public function setCallableRoute(
        string $method,
        string $path,
        callable $callable,
    ): static {
        // Get array builder keys.
        $keys = $this->getRouteKeys($method, $path);

        // Store callback.
        $this->validateCallback($callable);
        $callback = new Callback($callable, null, $this->activePreludes);
        $this->routes->setValue($keys, $callback);

        return $this;
    }

    /**
     * Store a route for the given class method.
     */
    public function setClassRoute(
        string $method,
        string $path,
        string $class_name,
        string $class_method,
        mixed ...$constructor_args,
    ): static {
        // Get array builder keys.
        $keys = $this->getRouteKeys($method, $path);

        // Create method reference.
        $reference = [$class_name, $class_method];
        $this->validateCallback($reference);
        $callback = new Callback(
            $reference,
            $constructor_args,
            $this->activePreludes,
        );
        $this->routes->setValue($keys, $callback);

        return $this;
    }

    /**
     * Store a route for the given closure.
     * 
     * @codeCoverageIgnore
     * @deprecated 2.0.0 Use `setCallableRoute()` instead.
     */
    public function setClosureRoute(
        string $method,
        string $path,
        \Closure $closure,
    ): static {
        return $this->setCallableRoute($method, $path, $closure);
    }

    /**
     * Store a route for the given closure.
     * 
     * @codeCoverageIgnore
     * @deprecated 2.0.0 Use `setClosureRoute()` instead.
     */
    public function setRoute(
        string $method,
        string $path,
        \Closure $callback,
    ): static {
        return $this->setClosureRoute($method, $path, $callback);
    }

    /**
     * Get route keys for a given path.
     * 
     * @return array<string>
     */
    protected function getRouteKeys(string $method, string $path): array
    {
        // Get prefix segments.
        $prefix = implode('/', $this->prefixes);
        $prefix_segments = strlen($prefix) > 0 ? explode('/', $prefix) : [];

        // Get path segments.
        $path = trim($path, '/');
        $segments = $path ? explode('/', $path) : [];
        $segments[] = '/';

        return [$method, ...$prefix_segments, ...$segments];
    }

    /**
     * Check whether a callback is eligible to be used as a route.
     */
    protected function validateCallback(array|callable $callback): void
    {
        // Validate parameter types.
        if (!$this->validator->validate($callback)) {
            $message = 'Cannot route callback due to invalid parameter types.';
            throw new \InvalidArgumentException($message);
        }

        // Get return type.
        $reflection = is_array($callback)
            ? new \ReflectionMethod(...$callback)
            : new \ReflectionFunction($callback);
        $return_type = $reflection->getReturnType();

        // Ensure the callback returns something.
        if ($return_type === null) {
            $message = 'Route callables must have a return type.';
            throw new \InvalidArgumentException($message);
        }

        // Check for a valid type.
        $this->validateReturnType($return_type);
    }

    /**
     * Check if a type is allowed as a callback return type.
     * 
     * Will throw an exception if an invalid type is found.
     */
    protected function validateReturnType(\ReflectionType $type): void
    {
        // Validate named type.
        if ($type instanceof \ReflectionNamedType) {
            // Check if type name is allowed.
            $name = $type->getName();
            if (!in_array($name, static::ALLOWED_RETURN_TYPES, true)) {
                $message = "Invalid return type {$type}: Allowed types are "
                    . implode(', ', static::ALLOWED_RETURN_TYPES) . ".";
                throw new \InvalidArgumentException($message);
            }
            return;
        }
        
        // Validate each type from union types.
        if ($type instanceof \ReflectionUnionType) {
            foreach ($type->getTypes() as $subtype) {
                $this->validateReturnType($subtype);
            }
            return;
        }

        // Cannot use intersection types.
        $message = "Invalid return type {$type}: Only named and union"
            . " return types are supported.";
        throw new \InvalidArgumentException($message);
    }
}
