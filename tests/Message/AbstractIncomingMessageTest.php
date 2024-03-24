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

use Laucov\Http\Message\AbstractIncomingMessage;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Http\Message\AbstractIncomingMessage
 */
class AbstractIncomingMessageTest extends TestCase
{
    public function invalidHeaderProvider(): array
    {
        return [
            [[
                'Cache-Control' => 'must-understand, no-store',
                'X-Foobar' => ['foo', 'bar'],
                'Content-Length' => 44,
            ]],
            [[
                'Cache-Control' => 'must-understand, no-store',
                'Content-Length' => '44',
                'X-Foobar' => ['foo', ['bar']],
            ]],
            [[
                'Cache-Control' => 'must-understand, no-store',
                'Content-Length' => '44',
                'X-Foobar' => ['foo', 'bar'],
                3 => 'Hello, World!',
            ]],
        ];
    }

    /**
     * @covers ::__construct
     * @uses Laucov\Files\Resource\StringSource::__construct
     * @uses Laucov\Files\Resource\StringSource::read
     * @uses Laucov\Http\Message\AbstractMessage::getBody
     * @uses Laucov\Http\Message\AbstractMessage::getHeader
     * @uses Laucov\Http\Message\AbstractMessage::getHeaderAsList
     * @uses Laucov\Http\Message\AbstractMessage::getHeaderNames
     */
    public function testCanInstantiate(): void
    {
        // Create instance.
        $message = $this->getInstance([
            'content' => 'The quick brown fox jumps over the lazy dog.',
            'headers' => [
                'Cache-Control' => 'must-understand, no-store',
                'Content-Length' => '44',
            ],
            'protocol_version' => null,
        ]);

        // Check body.
        /** @var \Laucov\Files\Resource\StringSource */
        $body = $message->getBody();
        $this->assertNotNull($body);
        $this->assertSame('The quick', $body->read(9));

        // Check headers.
        $this->assertSame('44', $message->getHeader('Content-Length'));
        $list = $message->getHeaderAsList('Cache-Control');
        $this->assertCount(2, $list);
        $this->assertContains('must-understand', $list);
        $this->assertContains('no-store', $list);

        // Check header names.
        $header_names = $message->getHeaderNames();
        $this->assertIsArray($header_names);
        $this->assertCount(2, $header_names);
        $this->assertSame('Cache-Control', $header_names[0]);
        $this->assertSame('Content-Length', $header_names[1]);
    }

    /**
     * @covers ::__construct
     * @uses Laucov\Files\Resource\StringSource::__construct
     * @dataProvider invalidHeaderProvider
     */
    public function testMustPassValidHeaders(array $headers): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->getInstance([
            'content' => '',
            'headers' => $headers,
            'protocol_version' => null,
        ]);
    }

    /**
     * Get a mock for `AbstractIncomingMessage`.
     */
    protected function getInstance(array $arguments): AbstractIncomingMessage
    {
        $class_name = AbstractIncomingMessage::class;
        return $this->getMockForAbstractClass($class_name, $arguments);
    }
}
