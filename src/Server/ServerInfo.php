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

namespace Laucov\Http\Server;

use Laucov\Files\Resource\Uri;

/**
 * Stores server and execution environment information.
 */
class ServerInfo
{
    /**
     * Create the server information instance.
     */
    public function __construct(
        /**
         * Server information, such as the `$_SERVER` variable.
         */
        protected array $info,
    ) {
    }

    /**
     * Get a server in entry by its key.
     */
    public function get(string $name, mixed $default_value = null): mixed
    {
        return $this->info[$name] ?? $default_value;
    }

    /**
     * Get the request protocol name.
     */
    public function getProtocolName(): null|string
    {
        $protocol = $this->get('SERVER_PROTOCOL');

        return is_string($protocol) && str_contains($protocol, '/')
            ? explode('/', $protocol)[0]
            : null;
    }

    /**
     * Get the request URI object.
     */
    public function getRequestUri(): Uri
    {
        // Get scheme.
        $scheme = match (true) {
            !empty($this->get('HTTPS')) => 'https',
            default => strtolower($this->getProtocolName() ?? ''),
        };

        // Get host and path.
        $host = (string) $this->get('HTTP_HOST');
        $path = (string) $this->get('REQUEST_URI');

        // Build URI.
        $string = ($scheme ? "{$scheme}:" : '')
            . ($host ? "//{$host}" : '')
            . $path;

        return Uri::fromString($string);
    }

    /**
     * Get the request protocol name.
     */
    public function getProtocolVersion(): null|string
    {
        $protocol = $this->get('SERVER_PROTOCOL');

        return is_string($protocol) && str_contains($protocol, '/')
            ? explode('/', $protocol)[1]
            : null;
    }
}
