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

/**
 * Stores information about an HTTP route.
 */
class RouteClassMethod extends AbstractRouteCallable
{
    /**
     * Create the route closure instance.
     */
    public function __construct(
        string $class_name,
        string $method_name,
        mixed ...$constructor_args,
    ) {
        // Validate and store types.
        $reflection = new \ReflectionMethod($class_name, $method_name);
        $this->validate($reflection);

        // Create callback.
        $this->closure = $reflection->isStatic()
            ? \Closure::fromCallable([$class_name, $method_name])
            : function (mixed ...$args) use (
                $class_name,
                $method_name,
                $constructor_args,
            ) {
                // Instantiate and call method.
                $object = new $class_name(...$constructor_args);
                return $object->{$method_name}(...$args);
            };
    }
}
