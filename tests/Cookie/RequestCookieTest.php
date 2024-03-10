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

namespace Tests\Cookie;

use Laucov\Http\Cookie\RequestCookie;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Http\Cookie\RequestCookie
 */
class RequestCookieTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::__toString
     */
    public function testCanCreateAndGetString(): void
    {
        // Create instance.
        $cookie = new RequestCookie('foo', 'bar');
        $this->assertSame('foo', $cookie->name);
        $this->assertSame('bar', $cookie->value);

        // Get cookie string.
        $this->assertSame('foo=bar', strval($cookie));

        // Test with escapable characters.
        $cookie = new RequestCookie('usuário', 'João da Silva');
        $expected = 'usu%C3%A1rio=Jo%C3%A3o%20da%20Silva';
        $this->assertSame($expected, strval($cookie));
    }
}
