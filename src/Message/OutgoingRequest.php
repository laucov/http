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

use Laucov\Arrays\ArrayBuilder;
use Laucov\Http\Message\Traits\RequestTrait;
use Laucov\Files\Resource\Uri;

/**
 * Stores information about an outgoing request.
 */
class OutgoingRequest extends AbstractOutgoingMessage implements
    RequestInterface
{
    use RequestTrait;

    /**
     * Parsed URI parameters.
     */
    protected ArrayBuilder $parameters;

    /**
     * Parsed POST variables.
     */
    protected ArrayBuilder $postVariables;

    /**
     * Create the outgoing request instance.
     */
    public function __construct()
    {
        $this->parameters = new ArrayBuilder([]);
        $this->postVariables = new ArrayBuilder([]);
    }

    /**
     * Get the parameters.
     */
    public function getParameters(): ArrayBuilder
    {
        return $this->parameters;
    }

    /**
     * Get the POST variables.
     */
    public function getPostVariables(): ArrayBuilder
    {
        return $this->postVariables;
    }

    /**
     * Set the request method.
     */
    public function setMethod(string $method): static
    {
        $this->method = strtoupper($method);
        return $this;
    }

    /**
     * Set the request URI.
     */
    public function setUri(Uri $uri): static
    {
        $this->uri = $uri;
        return $this;
    }
}
