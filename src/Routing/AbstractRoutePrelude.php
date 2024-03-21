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

use Laucov\Http\Message\RequestInterface;
use Laucov\Http\Message\ResponseInterface;

/**
 * Runs procedures before a request is sent to its route.
 * 
 * @deprecated 2.0.0 Use `\Laucov\Http\Routing\Call\Interfaces\PreludeInterface` instead.
 */
abstract class AbstractRoutePrelude
{
    /**
     * Create the route prelude instance.
     */
    public function __construct(
        /**
         * Routed request.
         */
        protected RequestInterface $request,

        /**
         * Prelude parameters.
         */
        protected array $parameters,
    ) {
    }

    /**
     * Run the prelude procedures.
     * 
     * Returning other value than `null` should interrupt the request.
     */
    public abstract function run(): null|string|\Stringable|ResponseInterface;
}
