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

namespace Laucov\Http\Message\Traits;

use Laucov\Http\Cookie\ResponseCookie;

/**
 * Has properties and methods common to response objects.
 */
trait ResponseTrait
{
    /**
     * Cookies.
     * 
     * @var array<string, ResponseCookie>
     */
    protected array $cookies = [];

    /**
     * HTTP status code.
     */
    protected int $statusCode = 200;

    /**
     * HTTP status text.
     */
    protected string $statusText = 'OK';

    /**
     * Get a registered cookie.
     */
    public function getCookie(string $name): null|ResponseCookie
    {
        return $this->cookies[$name] ?? null;
    }

    /**
     * Get the response status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the response status text.
     */
    public function getStatusText(): string
    {
        return $this->statusText;
    }
}
