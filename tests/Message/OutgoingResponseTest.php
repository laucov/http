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

declare(strict_types=1);

namespace Tests\Message;

use Laucov\Http\Cookie\ResponseCookie;
use Laucov\Http\Message\OutgoingResponse;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Http\Message\OutgoingResponse
 */
class OutgoingResponseTest extends TestCase
{
    protected OutgoingResponse $response;

    protected function setUp(): void
    {
        $this->response = new OutgoingResponse();
    }

    /**
     * @covers ::getStatusCode
     * @covers ::getStatusText
     * @covers ::setStatus
     */
    public function testCanSetStatus(): void
    {
        $this->assertSame(
            $this->response,
            $this->response->setStatus(201, 'Created'),
        );
        $this->assertSame(201, $this->response->getStatusCode());
        $this->assertSame('Created', $this->response->getStatusText());
    }

    /**
     * @covers ::getCookie
     * @covers ::setCookie
     * @uses Laucov\Http\Cookie\AbstractCookie::__construct
     * @uses Laucov\Http\Cookie\ResponseCookie::__construct
     */
    public function testCanSetCookies(): void
    {
        $cookie = new ResponseCookie('cookie-a', 'foobar');
        $this->response->setCookie($cookie);
        $this->assertSame($cookie, $this->response->getCookie('cookie-a'));
        $this->assertNull($this->response->getCookie('cookie-b'));
    }
}
