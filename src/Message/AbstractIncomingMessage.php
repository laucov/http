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
 * Stores information about an HTTP incoming message.
 */
class AbstractIncomingMessage extends AbstractMessage
{
    /**
     * Create the incoming message instance.
     */
    public function __construct(
        mixed $content,
        array $headers = [],
        null|string $protocol_version = null,
    ) {
        // Set body.
        $this->body = new StringSource($content);

        // Set headers.
        foreach ($headers as $name => $value) {
            // Check if the header name is a string.
            if (!is_string($name)) {
                $message = 'All header names must be strings.';
                throw new \InvalidArgumentException($message);
            }
            // Check the header value.
            if (is_string($value)) {
                // Transform string into array.
                $value = [$value];
            } elseif (is_array($value)) {
                // Check each element.
                foreach ($value as $item) {
                    if (!is_string($item)) {
                        $msg = 'Header array values must only have strings.';
                        throw new \InvalidArgumentException($msg);
                    }
                }
            } else {
                $message = 'All header values must be strings or arrays.';
                throw new \InvalidArgumentException($message);
            }
            // Set values.
            $this->headers[strtolower($name)] = $value;
        }

        // Set protocol version.
        $this->protocolVersion = $protocol_version;
    }
}
