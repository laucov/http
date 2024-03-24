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
     * Get the first line for a header name.
     * 
     * @deprecated 2.0.0 Use `getHeaderLine()` instead.
     * @codeCoverageIgnore
     */
    public function getHeader(string $name): null|string
    {
        return $this->getHeaderLine($name);
    }

    /**
     * Get values for a header name as a list.
     * 
     * @return string[]
     */
    public function getHeaderAsList(string $name): array
    {
        // Get lines.
        $lines = $this->getHeaderLines($name);
        if (count($lines) < 1) {
            return $lines;
        }

        // Get and trim individual list values.
        $values = array_map(fn ($l) => explode(',', $l), $lines);
        $values = array_map('trim', array_merge(...$values));

        return $values;
    }

    /**
     * Get the first line for a header name.
     */
    public function getHeaderLine(string $name): null|string
    {
        $name = strtolower($name);
        return $this->headers[$name][0] ?? null;
    }

    /**
     * Get all lines for a header name.
     */
    public function getHeaderLines(string $name): array
    {
        $name = strtolower($name);
        return $this->headers[$name] ?? [];
    }

    /**
     * Get all registered header names.
     * 
     * @return string[]
     */
    public function getHeaderNames(): array
    {
        // Get names.
        $names = array_keys($this->headers);

        // Beautify names.
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
