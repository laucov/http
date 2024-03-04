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
use Laucov\Http\Server\ServerInfo;

/**
 * Stores routes and assign them to HTTP requests.
 */
class Router
{
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
     * Stored routes.
     */
    protected ArrayBuilder $routes;

    /**
     * Create the router instance.
     */
    public function __construct()
    {
        $this->routes = new ArrayBuilder([]);
    }

    /**
     * Find a route for the given request object.
     */
    public function findRoute(
        RequestInterface $request,
        null|ServerInfo $server = null,
    ): null|Route {
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
        if (!($result instanceof AbstractRouteCallable)) {
            // @codeCoverageIgnoreStart
            $message = 'Found an unexpected [%s] stored as a route callable.';
            throw new \RuntimeException(sprintf($message, gettype($result)));
            // @codeCoverageIgnoreEnd
        }

        // Fill function parameters.
        $parameters = [];
        $capture_index = 0;
        foreach ($result->parameterTypes as $type) {
            if ($type->name === 'string') {
                if ($type->isVariadic) {
                    // Add variadic string argument.
                    $slice = array_slice($captured_segments, $capture_index);
                    array_push($parameters, ...$slice);
                    $capture_index = count($captured_segments) - 1;
                } else {
                    // Add single string argument.
                    $parameters[] = $captured_segments[$capture_index];
                    $capture_index++;
                }
            } elseif (is_a($type->name, RequestInterface::class, true)) {
                // Add request dependency.
                $parameters[] = $request;
            } elseif (is_a($type->name, ServerInfo::class, true)) {
                // Add server info dependency.
                $parameters[] = $server;
            } else {
                // @codeCoverageIgnoreStart
                $message = 'Unexpected route closure parameter of type [%s].';
                throw new \RuntimeException(sprintf($message, $type->name));
                // @codeCoverageIgnoreEnd
            }
        }

        return new Route($result, $parameters);
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
     * Store a route for the given class method.
     */
    public function setClassRoute(
        string $method,
        string $path,
        string $class_name,
        string $class_method,
        mixed ...$constructor_args,
    ): static {
        // Get storage keys.
        $keys = $this->getRouteKeys($method, $path);

        // Store callable object.
        $route_callable = new RouteClassMethod(
            $class_name,
            $class_method,
            ...$constructor_args,
        );
        $this->routes->setValue($keys, $route_callable);

        return $this;
    }

    /**
     * Store a route for the given closure.
     */
    public function setClosureRoute(
        string $method,
        string $path,
        \Closure $callback,
    ): static {
        // Store a new route closure.
        $keys = $this->getRouteKeys($method, $path);
        $route_callable = new RouteClosure($callback);
        $this->routes->setValue($keys, $route_callable);
        
        return $this;
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
}
