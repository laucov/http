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
     * Provides Set-Cookie headers and expectations for the cookie properties.
     */
    public function headerProvider(): array
    {
        return [
            'Test with Expires and Path' => [
                'username=johndoe; Expires=Wed, 21 Oct 2020 07:28:00 GMT; '
                    . 'Path=/',
                [
                    'name' => 'username',
                    'value' => 'johndoe',
                    'domain' => null,
                    'expires' => 'Wed, 21 Oct 2020 07:28:00 GMT',
                    'httpOnly' => false,
                    'maxAge' => null,
                    'partitioned' => false,
                    'path' => '/',
                    'sameSite' => null,
                    'secure' => false,
                ],
            ],
            'Test with Max-Age, Secure and HttpOnly' => [
                'sessionid=abc123; Max-Age=3600; Secure; HttpOnly',
                [
                    'name' => 'sessionid',
                    'value' => 'abc123',
                    'domain' => null,
                    'expires' => null,
                    'httpOnly' => true,
                    'maxAge' => 3600,
                    'partitioned' => false,
                    'path' => null,
                    'sameSite' => null,
                    'secure' => true,
                ],
            ],
            'Test with Domain and SameSite' => [
                'lang=en-US; Domain=.example.com; SameSite=Strict',
                [
                    'name' => 'lang',
                    'value' => 'en-US',
                    'domain' => '.example.com',
                    'expires' => null,
                    'httpOnly' => false,
                    'maxAge' => null,
                    'partitioned' => false,
                    'path' => null,
                    'sameSite' => SameSite::STRICT,
                    'secure' => false,
                ],
            ],
            'Test with Partitioned, Max-Age and SameSite' => [
                'userId=12345; Partitioned; Max-Age=3600; SameSite=Lax',
                [
                    'name' => 'userId',
                    'value' => '12345',
                    'domain' => null,
                    'expires' => null,
                    'httpOnly' => false,
                    'maxAge' => 3600,
                    'partitioned' => true,
                    'path' => null,
                    'sameSite' => SameSite::LAX,
                    'secure' => false,
                ],
            ],
            'Test with URL encoded characters' => [
                'preferences=%7B%22theme%22%3A%22light%22%2C%22language%22%3A%'
                    . '22en%22%7D; Max-Age=604800; SameSite=Strict',
                [
                    'name' => 'preferences',
                    'value' => '{"theme":"light","language":"en"}',
                    'domain' => null,
                    'expires' => null,
                    'httpOnly' => false,
                    'maxAge' => 604800,
                    'partitioned' => false,
                    'path' => null,
                    'sameSite' => SameSite::STRICT,
                    'secure' => false,
                ],
            ],

        ];
    }

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

    /**
     * @covers ::createFromHeader
     * @uses Laucov\Http\Cookie\AbstractCookie::__construct
     * @uses Laucov\Http\Cookie\ResponseCookie::__construct
     * @dataProvider headerProvider
     */
    public function testCanCreateFromHeader(
        string $header_line,
        array $properties,
    ): void {
        // Create the cookie.
        $cookie = ResponseCookie::createFromHeader($header_line);

        // Check properties.
        foreach ($properties as $name => $value) {
            $message = 'Assert that $cookie->%s is %s.';
            $message = sprintf($message, $name, var_export($value, true));
            $this->assertSame($value, $cookie->{$name}, $message);
        }
    }
}
