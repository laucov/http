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

use Laucov\Http\Cookie\ResponseCookie;
use Laucov\Http\Cookie\SameSite;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Http\Cookie\ResponseCookie
 */
class ResponseCookieTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::__toString
     * @uses Laucov\Http\Cookie\AbstractCookie::__construct
     */
    public function testCanCreateAndGetString(): void
    {
        // Create instance.
        $cookie = new ResponseCookie(
            'foo',
            'bar',
            'foobar.com',
            'Sat, 9 Mar 2024 23:24:25 GMT',
            true,
            null,
            true,
            '/path/to',
            SameSite::STRICT,
            true,
        );

        // Get string.
        $expected = 'foo=bar; '
            . 'Domain=foobar.com; '
            . 'Expires=Sat, 9 Mar 2024 23:24:25 GMT; '
            . 'HttpOnly; '
            . 'Partitioned; '
            . 'Path=/path/to; '
            . 'SameSite=Strict; '
            . 'Secure';
        $this->assertSame($expected, strval($cookie));

        // Test scaping and default values.
        $cookie = new ResponseCookie('fóôbâr', 'bàz');
        $expected = 'f%C3%B3%C3%B4b%C3%A2r=b%C3%A0r';

        // Test Max-Age precedence over expires.
        $cookie = new ResponseCookie(
            'foo',
            'bar',
            expires: 'now',
            maxAge: 12345,
        );
        $expected = 'foo=bar; Max-Age=12345';
        $this->assertSame($expected, strval($cookie));
    }
}
