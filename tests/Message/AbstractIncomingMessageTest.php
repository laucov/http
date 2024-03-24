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

use Laucov\Files\Resource\StringSource;
use Laucov\Http\Message\AbstractIncomingMessage;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Http\Message\AbstractIncomingMessage
 */
class AbstractIncomingMessageTest extends TestCase
{
    /**
     * Provide a list of invalid header sets.
     */
    public function invalidHeaderProvider(): array
    {
        return [
            // Invalid "Content-Lenght" with non-string value.
            [[
                'Cache-Control' => 'must-understand, no-store',
                'X-Foobar' => ['foo', 'bar'],
                'Content-Length' => 44,
            ]],
            // Invalid "X-Foobar" with non-string list value.
            [[
                'Cache-Control' => 'must-understand, no-store',
                'Content-Length' => '44',
                'X-Foobar' => ['foo', ['bar']],
            ]],
            // Invalid "3" with integer key.
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
     * @covers ::getBody
     * @covers ::getHeaderAsList
     * @covers ::getHeaderLine
     * @covers ::getHeaderLines
     * @covers ::getHeaderNames
     * @covers ::getProtocolVersion
     * @uses Laucov\Files\Resource\StringSource::__construct
     * @uses Laucov\Files\Resource\StringSource::read
     */
    public function testCanInstantiate(): void
    {
        // Set arguments.
        $arguments = [
            'content' => 'The quick brown fox jumps over the lazy dog.',
            'headers' => [
                // Set single line header.
                'Content-Length' => '44',
                // Set multi-line header.
                'SET-COOKIE' => [
                    'foo=a; Secure',
                    'bar=b',
                ],
                // Set a single line value list.
                'AcCePt-LaNgUaGe' => 'fr-CH, fr;q=0.9, en;q=0.8',
                // Set a multi-line value list.
                'cache-control' => [
                    'no-cache, no-store',
                    'must-revalidate',
                ],
            ],
            'protocol_version' => '1.1',
        ];

        // Create instance.
        $message = $this->getMockForAbstractClass(
            AbstractIncomingMessage::class,
            $arguments,
        );

        // Get body content.
        /** @var \Laucov\Files\Resource\StringSource */
        $body = $message->getBody();
        $this->assertIsObject($body);

        // Test body reading.
        $this->assertSame('The quick', $body->read(9));
        $this->assertSame(' brown fox jumps over ', $body->read(22));
        $this->assertSame('the lazy dog.', $body->read(13));
        $this->assertSame('', $body->read(10));

        // Test stringifying the body.
        $this->assertSame(
            'The quick brown fox jumps over the lazy dog.',
            (string) $body,
        );

        // Test getting single lines.
        // Authorization - not set
        $this->assertNull($message->getHeaderLine('Authorization'));
        // Content-Length
        $this->assertSame('44', $message->getHeaderLine('Content-Length'));
        // Set-Cookie
        $this->assertSame(
            'foo=a; Secure',
            $message->getHeaderLine('Set-Cookie'),
        );
        // Accept-Language
        $this->assertSame(
            'fr-CH, fr;q=0.9, en;q=0.8',
            $message->getHeaderLine('Accept-Language'),
        );
        // Cache-Control
        $this->assertSame(
            'no-cache, no-store',
            $message->getHeaderLine('Cache-Control'),
        );

        // Test getting multiple lines.
        // Authorization - not set
        $lines = $message->getHeaderLines('Authorization');
        $this->assertIsArray($lines);
        $this->assertCount(0, $lines);
        // Content-Length
        $lines = $message->getHeaderLines('Content-Length');
        $this->assertIsArray($lines);
        $this->assertCount(1, $lines);
        $this->assertSame('44', $lines[0]);
        // Set-Cookie
        $lines = $message->getHeaderLines('Set-Cookie');
        $this->assertIsArray($lines);
        $this->assertCount(2, $lines);
        $this->assertSame('foo=a; Secure', $lines[0]);
        $this->assertSame('bar=b', $lines[1]);
        // Accept-Language
        $lines = $message->getHeaderLines('Accept-Language');
        $this->assertIsArray($lines);
        $this->assertCount(1, $lines);
        $this->assertSame('fr-CH, fr;q=0.9, en;q=0.8', $lines[0]);
        // Cache-Control
        $lines = $message->getHeaderLines('Cache-Control');
        $this->assertIsArray($lines);
        $this->assertCount(2, $lines);
        $this->assertSame('no-cache, no-store', $lines[0]);
        $this->assertSame('must-revalidate', $lines[1]);

        // Test getting value list.
        // Authorization - not set
        $values = $message->getHeaderAsList('Authorization');
        $this->assertIsArray($values);
        $this->assertCount(0, $values);
        // Content-Length
        $values = $message->getHeaderAsList('Content-Length');
        $this->assertIsArray($values);
        $this->assertCount(1, $values);
        $this->assertSame('44', $values[0]);
        // Set-Cookie
        $values = $message->getHeaderAsList('Set-Cookie');
        $this->assertIsArray($values);
        $this->assertCount(2, $values);
        $this->assertSame('foo=a; Secure', $values[0]);
        $this->assertSame('bar=b', $values[1]);
        // Accept-Language
        $values = $message->getHeaderAsList('Accept-Language');
        $this->assertIsArray($values);
        $this->assertCount(3, $values);
        $this->assertSame('fr-CH', $values[0]);
        $this->assertSame('fr;q=0.9', $values[1]);
        $this->assertSame('en;q=0.8', $values[2]);
        // Cache-Control
        $values = $message->getHeaderAsList('Cache-Control');
        $this->assertCount(3, $values);
        $this->assertSame('no-cache', $values[0]);
        $this->assertSame('no-store', $values[1]);
        $this->assertSame('must-revalidate', $values[2]);

        // Test getting header names.
        $names = $message->getHeaderNames();
        $this->assertIsArray($names);
        $this->assertCount(4, $names);
        $this->assertSame('Content-Length', $names[0]);
        $this->assertSame('Set-Cookie', $names[1]);
        $this->assertSame('Accept-Language', $names[2]);
        $this->assertSame('Cache-Control', $names[3]);

        // Test getting protocol version.
        $this->assertSame('1.1', $message->getProtocolVersion());
    }

    // @todo Test case sensitivity
    // public function testIsCaseInsensitive(): void
    // {
    // }

    /**
     * @covers ::__construct
     * @uses Laucov\Files\Resource\StringSource::__construct
     * @dataProvider invalidHeaderProvider
     */
    public function testMustPassValidHeaders(array $headers): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->getMockForAbstractClass(AbstractIncomingMessage::class, [
            'content' => 'Some useless content.',
            'headers' => $headers,
            'protocol_version' => null,
        ]);
    }
}
