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

/**
 * Stores information about an HTTP route callback.
 */
class Callback
{
    /**
     * Callback error.
     * 
     * A class method was passed as a callback without constructor arguments.
     */
    public const ERROR_NO_CONSTRUCTOR_ARGS = 1;

    /**
     * Callback error.
     * 
     * One or more prelude names are not strings.
     */
    public const ERROR_INVALID_PRELUDE_NAMES = 2;

    /**
     * Create the callback instance.
     * 
     * @param array|callable $callback
     * @param null|array $constructorArgs
     * @param array<string> $preludeNames
     */
    public function __construct(
        /**
         * Callback callable or class method.
         * 
         * @var array<string>|callable
         */
        public readonly mixed $callback,

        /**
         * Class constructor arguments.
         * 
         * Used only when `$callback` is an array.
         */
        public readonly null|array $constructorArgs,

        /**
         * Registered preludes.
         * 
         * @var array<string>
         */
        public readonly array $preludeNames,
    ) {
        // Validate constructor arguments for class calls.
        if (!is_callable($this->callback) && $this->constructorArgs === null) {
            $msg = 'Class method callbacks require constructor arguments.';
            $code = static::ERROR_NO_CONSTRUCTOR_ARGS;
            throw new \InvalidArgumentException($msg, $code);
        }

        // Validate prelude names.
        foreach ($this->preludeNames as $prelude_name) {
            if (!is_string($prelude_name)) {
                $msg = 'Non-string prelude name passed to callback.';
                $code = static::ERROR_INVALID_PRELUDE_NAMES;
                throw new \InvalidArgumentException($msg, $code);
            }
        }
    }
}
