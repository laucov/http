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
interface MessageInterface
{
    /**
     * Supported protocol versions.
     */
    public const PROTOCOL_VERSIONS = ['1.0', '1.1', '2', '3'];

    /**
     * Get the message body.
     */
    public function getBody(): null|StringSource;

    /**
     * Get the first line for a header name.
     * 
     * @deprecated 2.0.0 Use `getHeaderLine()` instead.
     * @codeCoverageIgnore
     */
    public function getHeader(string $name): null|string;

    /**
     * Get values for a header name as a list.
     * 
     * @return string[]
     */
    public function getHeaderAsList(string $name): array;

    /**
     * Get the first line for a header name.
     */
    public function getHeaderLine(string $name): null|string;

    /**
     * Get all lines for a header name.
     * 
     * @return string[]
     */
    public function getHeaderLines(string $name): array;

    /**
     * Get all registered header names.
     * 
     * @return string[]
     */
    public function getHeaderNames(): array;

    /**
     * Get the HTTP protocol version.
     */
    public function getProtocolVersion(): null|string;
}
