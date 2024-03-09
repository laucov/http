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

namespace Laucov\Http\Cookie;

/**
 * Stores data for a response cookie, usually sent in the "Set-Cookie" header.
 */
class ResponseCookie extends AbstractCookie
{
    /**
     * Create the cookie instance.
     */
    public function __construct(
        string $name,
        string $value,

        /**
         * Host to which the cookie will be sent.
         */
        public null|string $domain = null,

        /**
         * Cookie maximum lifetime.
         * 
         * Must be an HTTP-date timestamp.
         * 
         * Is ignored if `$maxAge` is set.
         */
        public null|string $expires = null,

        /**
         * Whether JavaScript should be forbidden to access the cookie.
         */
        public bool $httpOnly = false,

        /**
         * Number of seconds until the cookie expires.
         * 
         * Has precedence over `$expires` if both are set.
         */
        public null|int $maxAge = null,

        /**
         * Whether the cookie should be stored using partitioned storage.
         */
        public bool $partitioned = false,

        /**
         * Path in which the browser should send the "Cookie" header.
         * 
         * Subdirectories are matched as well.
         */
        public null|string $path = null,

        /**
         * Whether this cookie should be sent in cross-site requests.
         */
        public null|SameSite $sameSite = null,

        /**
         * Whether to restrict this cookie to secure HTTP (HTTPS) requests.
         */
        public bool $secure = false,
    ) {
        parent::__construct($name, $value);
    }

    /**
     * Get the cookie string representation.
     */
    public function __toString(): string
    {
        // Encode name and value.
        $name = rawurlencode($this->name);
        $value = rawurlencode($this->value);
        $cookie = "{$name}={$value}";

        // Add domain.
        if ($this->domain !== null) {
            $cookie .= '; Domain=' . $this->domain;
        }

        // Add expiration.
        if ($this->maxAge !== null) {
            $cookie .= '; Max-Age=' . $this->maxAge;
        } elseif ($this->expires !== null) {
            $cookie .= '; Expires=' . $this->expires;
        }

        // Add HttpOnly directive.
        if ($this->httpOnly) {
            $cookie .= '; HttpOnly';
        }

        // Add Partitioned directive.
        if ($this->partitioned) {
            $cookie .= '; Partitioned';
        }

        // Add path.
        if ($this->path !== null) {
            $cookie .= '; Path=' . $this->path;
        }

        // Add Strict directive.
        if ($this->sameSite !== null) {
            $cookie .= '; SameSite=' . $this->sameSite->value;
        }

        // Add Secure directive.
        if ($this->secure) {
            $cookie .= '; Secure';
        }

        return $cookie;
    }
}
