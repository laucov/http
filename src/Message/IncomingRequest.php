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

use Laucov\Arrays\ArrayReader;
use Laucov\Http\Cookie\RequestCookie;
use Laucov\Http\Message\Traits\RequestTrait;
use Laucov\Files\Resource\Uri;

/**
 * Stores information about an incoming request.
 */
class IncomingRequest extends AbstractIncomingMessage implements
    RequestInterface
{
    use RequestTrait;

    /**
     * Parsed URI parameters.
     */
    protected ArrayReader $parameters;

    /**
     * Parsed POST variables.
     */
    protected ArrayReader $postVariables;

    /**
     * Create the outgoing request instance.
     */
    public function __construct(
        mixed $content_or_post,
        array $headers,
        null|string $protocol_version,
        string $method,
        string|Uri $uri,
        array $parameters,
        array $cookies,
    ) {
        // Set parameters.
        $this->parameters = new ArrayReader($parameters);
        $this->method = $method;
        $this->uri = is_string($uri) ? Uri::fromString($uri) : $uri;

        // Set POST variables and run the parent's constructor.
        if (is_array($content_or_post)) {
            $this->postVariables = new ArrayReader($content_or_post);
            parent::__construct('', $headers, $protocol_version);
        } else {
            $this->postVariables = new ArrayReader([]);
            parent::__construct($content_or_post, $headers, $protocol_version);
        }

        // Set cookies.
        foreach ($cookies as $name => $value) {
            if (!is_string($value)) {
                $msg = 'All cookie values must be strings. Found a value '
                    . "of type %s in cookie named \"{$name}\".";
                $type = gettype($value);
                throw new \InvalidArgumentException(sprintf($msg, $type));
            }
            $this->cookies[$name] = new RequestCookie($name, $value);
        }
    }

    /**
     * Get the parameters.
     */
    public function getParameters(): ArrayReader
    {
        return $this->parameters;
    }

    /**
     * Get the POST variables.
     */
    public function getPostVariables(): ArrayReader
    {
        return $this->postVariables;
    }
}
