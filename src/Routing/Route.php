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

use Laucov\Http\Message\OutgoingResponse;
use Laucov\Http\Message\ResponseInterface;
use Laucov\Http\Routing\Call\Interfaces\RouteInterface;

/**
 * Stores information about an HTTP route.
 */
class Route implements RouteInterface
{
    /**
     * Route closure.
     * 
     * @deprecated 2.0.0 Use `$routeCallable` instead.
     */
    protected AbstractRouteCallable $routeClosure;

    /**
     * Create the route instance.
     */
    public function __construct(
        /**
         * Route closure.
         */
        protected AbstractRouteCallable $routeCallable,

        /**
         * Execution parameters.
         */
        protected array $parameters,

        /**
         * Prelude objects.
         * 
         * @var array<AbstractRoutePrelude>
         */
        protected array $preludes = [],
    ) {
        $this->routeClosure = &$this->routeCallable;
    }

    /**
     * Run the route's closure with the given arguments.
     */
    public function run(): ResponseInterface
    {
        // Run prelude instances.
        foreach ($this->preludes as $prelude) {
            $p_result = $prelude->run();
            if ($p_result !== null) {
                return $this->createResponse($p_result);
            }
        }

        // Get closure results.
        $result = call_user_func_array(
            $this->routeClosure->closure,
            $this->parameters,
        );

        // Get response.
        $response = $this->createResponse($result);

        return $response;
    }

    /**
     * Transform unknown output into a `ResponseInterface` object.
     */
    protected function createResponse(
        string|\Stringable|ResponseInterface $content,
    ): ResponseInterface {
        // Check content type.
        if ($content instanceof ResponseInterface) {
            // Return response.
            return $content;
        } elseif (is_string($content) || $content instanceof \Stringable) {
            // Create response from string.
            $response = new OutgoingResponse();
            return $response->setBody((string) $content);
        } else {
            // @codeCoverageIgnoreStart
            $message = 'Received an unexpected result from a route closure.';
            throw new \RuntimeException($message);
            // @codeCoverageIgnoreEnd
        }
    }
}
