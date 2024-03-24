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
     * Treat a header as a comma-separated list and push a value to it.
     * 
     * Adds the value to the last registered line for the given header name.
     * 
     * If not registered, creates the header.
     * 
     * @deprecated 2.0.0 Use `addHeaderValue()` instead.
     * @codeCoverageIgnore
     */
    public function addHeader(string $name, string $value): static
    {
        return $this->addHeaderValue($name, $value);
    }

    /**
     * Set a value as a new line for the given header name.
     */
    public function addHeaderLine(string $name, string $value): static
    {
        $name = strtolower($name);
        $this->headers[$name][] = trim($value);
        return $this;
    }

    /**
     * Treat a header as a comma-separated list and push a value to it.
     * 
     * Adds the value to the last registered line for the given header name.
     * 
     * If not registered, creates the header.
     */
    public function addHeaderValue(string $name, string $value): static
    {
        // Add as a new header line if not set yet.
        $name = strtolower($name);
        if (!array_key_exists($name, $this->headers)) {
            return $this->setHeaderLine($name, $value);
        }

        // Get line values.
        $line = array_pop($this->headers[$name]);
        $values = array_map('trim', explode(',', $line));

        // Add new value and set line.
        $values[] = trim($value);
        $this->headers[$name][] = implode(', ', $values);

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
     * Set a value as the only line for the given header name.
     * 
     * @deprecated 2.0.0 Use `setHeaderLine()` instead.
     * @codeCoverageIgnore
     */
    public function setHeader(string $name, string $value): static
    {
        return $this->setHeaderLine($name, $value);
    }

    /**
     * Set a value as the only line for the given header name.
     */
    public function setHeaderLine(string $name, string $value): static
    {
        $name = strtolower($name);
        $this->headers[$name] = [trim($value)];
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
