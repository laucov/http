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

namespace Laucov\Http\Routing\Call;

use Laucov\Http\Message\OutgoingResponse;
use Laucov\Http\Message\ResponseInterface;
use Laucov\Http\Routing\Call\Interfaces\PreludeInterface;
use Laucov\Http\Routing\Call\Interfaces\RouteInterface;

/**
 * Stores information about a processed route callback.
 */
class Route implements RouteInterface
{
    /**
     * Create the route instance.
     */
    public function __construct(
        /**
         * Callback object.
         */
        protected Callback $callback,

        /**
         * Callback parameters.
         */
        protected array $parameters,

        /**
         * Route preludes.
         * 
         * @var array<PreludeInterface>
         */
        protected array $preludes,
    ) {
    }

    /**
     * Run the route procedures.
     */
    public function run(): ResponseInterface
    {
        // Run preludes.
        foreach ($this->preludes as $prelude) {
            $prelude_result = $prelude->run();
            if ($prelude_result !== null) {
                return $prelude_result instanceof ResponseInterface
                    ? $prelude_result
                    : $this->createResponse($prelude_result);
            }
        }

        // Run callback.
        $callback = $this->callback->callback;
        if (is_callable($callback)) {
            // Execute callable.
            $result = $callback(...$this->parameters);
        } else {
            // Instantiate class and call method.
            [$class_name, $method_name] = $callback;
            $instance = new $class_name(...$this->callback->constructorArgs);
            $result = $instance->{$method_name}(...$this->parameters);
        }

        // Process result.
        return $result instanceof ResponseInterface
            ? $result
            : $this->createResponse($result);
    }

    /**
     * Handle unknown output and return it as a `ResponseInterface` object.
     */
    protected function createResponse(mixed $content): ResponseInterface
    {
        // Handle string or stringable object.
        if (is_string($content) || $content instanceof \Stringable) {
            // Create response from string.
            $response = new OutgoingResponse();
            return $response->setBody((string) $content);
        }

        // Fail if received other type of content.
        $message = 'Received an unexpected result from a route closure.';
        throw new \RuntimeException($message);
    }
}
