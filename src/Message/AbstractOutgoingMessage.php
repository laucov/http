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
 * Stores information about an HTTP outgoing message.
 */
abstract class AbstractOutgoingMessage extends AbstractMessage
{
    /**
     * Add a message header.
     * 
     * If the header exists, appends the value, otherwise creates it.
     */
    public function addHeader(
        string $name,
        string $value,
        int $index = 0,
    ): static {
        $name = strtolower($name);
        $list = $this->getHeaderAsList($name);

        if ($list === null) {
            return $this->setHeader($name, $value);
        }

        $list[] = trim($value);
        $this->headers[$name][$index] = implode(', ', $list);

        return $this;
    }
    /**
     * Set the message body.
     * 
     * @param string|resource $content
     */
    public function setBody(mixed $content): static
    {
        $this->body = new StringSource($content);
        return $this;
    }

    /**
     * Set a message header.
     */
    public function setHeader(
        string $name,
        string $value,
        bool $replace = true,
    ): static {
        // Format name and value.
        $name = strtolower($name);
        $value = trim($value);

        // Add or replace header.
        if ($replace) {
            $this->headers[$name] = [$value];
        } else {
            $this->headers[$name][] = $value;
        }

        return $this;
    }

    /**
     * Set the HTTP protocol version.
     */
    public function setProtocolVersion(null|string $version): static
    {
        if (!in_array($version, static::PROTOCOL_VERSIONS, true)) {
            $versions = implode(', ', static::PROTOCOL_VERSIONS);
            $message = 'Unknown HTTP version "%s". Supported values: %s.';
            throw new \InvalidArgumentException(
                sprintf($message, $version, $versions),
            );
        }
        $this->protocolVersion = $version;

        return $this;
    }
}
