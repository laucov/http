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
use Laucov\Http\Message\IncomingResponse;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Http\Message\IncomingResponse
 */
class IncomingResponseTest extends TestCase
{
    /**
     * @covers ::__construct
     * @uses Laucov\Http\Cookie\AbstractCookie::__construct
     * @uses Laucov\Http\Cookie\ResponseCookie::__construct
     * @uses Laucov\Http\Message\AbstractIncomingMessage::__construct
     */
    public function testCookiesMustBeResponseCookieObjects(): void
    {
        new IncomingResponse('', [], null, 200, '', [
            new ResponseCookie('cookie-a', 'A'),
            new ResponseCookie('cookie-b', 'B'),
        ]);
        $this->expectException(\InvalidArgumentException::class);
        new IncomingResponse('', [], null, 200, '', [
            new ResponseCookie('cookie-a', 'A'),
            new \stdClass(),
        ]);
    }

    /**
     * @covers ::__construct
     * @uses Laucov\Files\Resource\StringSource::__construct
     * @uses Laucov\Files\Resource\StringSource::read
     * @uses Laucov\Http\Cookie\AbstractCookie::__construct
     * @uses Laucov\Http\Cookie\ResponseCookie::__construct
     * @uses Laucov\Http\Message\AbstractIncomingMessage::__construct
     * @uses Laucov\Http\Message\AbstractMessage::getBody
     * @uses Laucov\Http\Message\AbstractMessage::getHeaderLine
     * @uses Laucov\Http\Message\AbstractMessage::getProtocolVersion
     * @uses Laucov\Http\Message\Traits\ResponseTrait::getCookie
     * @uses Laucov\Http\Message\Traits\ResponseTrait::getCookieNames
     * @uses Laucov\Http\Message\Traits\ResponseTrait::getStatusCode
     * @uses Laucov\Http\Message\Traits\ResponseTrait::getStatusText
     */
    public function testSetsPropertiesFromConstructor(): void
    {
        // Create cookies.
        $cookies = [
            new ResponseCookie('cookie-1', 'Cookie 1 text'),
            new ResponseCookie('cookie-2', 'Cookie 2 text'),
        ];

        // Create response.
        $response = new IncomingResponse(
            content: 'Some message.',
            headers: [
                'Authorization' => 'Basic user:password',
            ],
            protocol_version: '1.1',
            status_code: 401,
            status_text: 'Unauthorized',
            cookies: [$cookies[0], $cookies[1]],
        );

        // Test body.
        $this->assertSame('Some message.', (string) $response->getBody());

        // Test protocol version.
        $this->assertSame('1.1', $response->getProtocolVersion());

        // Test headers.
        $header = $response->getHeaderLine('Authorization');
        $this->assertSame('Basic user:password', $header);

        // Test status.
        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('Unauthorized', $response->getStatusText());

        // Test cookies.
        $this->assertSame($cookies[0], $response->getCookie('cookie-1'));
        $this->assertSame($cookies[1], $response->getCookie('cookie-2'));
        $cookie_names = $response->getCookieNames();
        $this->assertIsArray($cookie_names);
        $this->assertCount(2, $cookie_names);
        $this->assertSame('cookie-1', $cookie_names[0]);
        $this->assertSame('cookie-2', $cookie_names[1]);
    }
}
