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

use Laucov\Http\Message\Traits\ResponseTrait;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Http\Message\Traits\ResponseTrait
 */
class ResponseTraitTest extends TestCase
{
    /**
     * @var ResponseTrait
     */
    protected object $response;

    /**
     * @covers ::getCookie
     * @covers ::getCookieNames
     */
    public function testCanGetCookies(): void
    {
        // Test default cookies.
        $this->assertNull($this->response->getCookie('name'));
        $names = $this->response->getCookieNames();
        $this->assertIsArray($names);
        $this->assertCount(0, $names);
    }

    /**
     * @covers ::getStatusCode
     * @covers ::getStatusText
     */
    public function testCanGetStatus(): void
    {
        // Test default status code.
        $this->assertSame(200, $this->response->getStatusCode());
        // Test default status text.
        $this->assertSame('OK', $this->response->getStatusText());
    }

    protected function setUp(): void
    {
        $this->response = $this->getMockForTrait(ResponseTrait::class);
    }
}
