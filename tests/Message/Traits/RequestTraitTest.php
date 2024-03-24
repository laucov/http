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

use Laucov\Http\Message\Traits\RequestTrait;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Http\Message\Traits\RequestTrait
 */
class RequestTraitTest extends TestCase
{
    /**
     * @var RequestTrait
     */
    protected object $request;

    /**
     * @covers ::getMethod
     */
    public function testCanGetMethod(): void
    {
        // Test default method value.
        $this->assertSame('GET', $this->request->getMethod());
    }

    /**
     * @covers ::getCookie
     * @covers ::getCookieNames
     */
    public function testCanGetCookies(): void
    {
        // Test default cookies.
        $this->assertNull($this->request->getCookie('name'));
        $names = $this->request->getCookieNames();
        $this->assertIsArray($names);
        $this->assertCount(0, $names);
    }

    /**
     * @covers ::getUri
     */
    public function testUriMustBeInitialized(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->request->getUri();
    }

    protected function setUp(): void
    {
        $this->request = $this->getMockForTrait(RequestTrait::class);
    }
}
