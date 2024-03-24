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

namespace Laucov\Http\Message;

use Laucov\Files\Resource\StringSource;

/**
 * Stores information about an HTTP message.
 */
abstract class AbstractMessage implements MessageInterface
{
    /**
     * Stored message body.
     */
    protected null|StringSource $body = null;

    /**
     * Stored headers.
     * 
     * @var array<string, string[]>
     */
    protected array $headers = [];

    /**
     * HTTP protocol version.
     */
    protected null|string $protocolVersion = null;

    /**
     * Get the message body.
     */
    public function getBody(): null|StringSource
    {
        return $this->body;
    }

    /**
     * Get a header value.
     */
    public function getHeader(string $name): null|string
    {
        $name = strtolower($name);
        return isset($this->headers[$name][0])
            ? $this->headers[$name][0]
            : null;
    }

    /**
     * Get a header list of values.
     * 
     * @return string[]
     */
    public function getHeaderAsList(string $name): null|array
    {
        // Get header text.
        $header = $this->getHeader($name);
        if ($header === null) {
            return null;
        }

        // Split and trim.
        $values = explode(',', $header);
        return array_map('trim', $values);
    }

    /**
     * Get all registered header names.
     */
    public function getHeaderNames(): array
    {
        // Get names.
        $names = array_keys($this->headers);

        // Beautify.
        foreach ($names as &$name) {
            $name = implode('-', (array_map('ucfirst', explode('-', $name))));
        }

        return $names;
    }

    /**
     * Get the HTTP protocol version.
     */
    public function getProtocolVersion(): null|string
    {
        return $this->protocolVersion;
    }
}
