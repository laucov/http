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
use Laucov\Http\Message\AbstractOutgoingMessage;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Http\Message\AbstractOutgoingMessage
 */
class AbstractOutgoingMessageTest extends TestCase
{
    private AbstractOutgoingMessage $message;

    protected function setUp(): void
    {
        $class_name = AbstractOutgoingMessage::class;
        $this->message = $this->getMockForAbstractClass($class_name);
    }

    /**
     * @covers ::addHeaderLine
     * @covers ::addHeaderValue
     * @covers ::getHeaderAsList
     * @covers ::getHeaderLine
     * @covers ::getHeaderLines
     * @covers ::setHeaderLine
     */
    public function testCanAddAndSetHeaders(): void
    {
        // Add values.
        $this->message->addHeaderValue('Accept-Language', 'pt-BR');
        $this->message->addHeaderValue('Accept-Language', 'pt;q=0.9');

        // Add new line.
        $this->message->addHeaderLine('Accept-Language', 'es;q=0.8, en;q=0.7');

        // Add value to new line.
        $this->message->addHeaderValue('Accept-Language', 'fr;q=0.6');

        // Test getting single line.
        $this->assertSame(
            'pt-BR, pt;q=0.9',
            $this->message->getHeaderLine('Accept-Language'),
        );

        // Test getting all lines.
        $lines = $this->message->getHeaderLines('Accept-Language');
        $this->assertIsArray($lines);
        $this->assertCount(2, $lines);
        $this->assertSame('pt-BR, pt;q=0.9', $lines[0]);
        $this->assertSame('es;q=0.8, en;q=0.7, fr;q=0.6', $lines[1]);

        // Test getting values.
        $values = $this->message->getHeaderAsList('Accept-Language');
        $this->assertIsArray($values);
        $this->assertCount(5, $values);
        $this->assertSame('pt-BR', $values[0]);
        $this->assertSame('pt;q=0.9', $values[1]);
        $this->assertSame('es;q=0.8', $values[2]);
        $this->assertSame('en;q=0.7', $values[3]);
        $this->assertSame('fr;q=0.6', $values[4]);

        // Set line for unset header.
        $this->message->setHeaderLine('Authorization', 'Basic john:1234');
        // Set line for existing header - overwrite all set lines.
        $this->message->setHeaderLine('Accept-Language', 'en-US, en');

        // Test new values.
        // Authorization
        $this->assertSame(
            'Basic john:1234',
            $this->message->getHeaderLine('Authorization'),
        );
        $lines = $this->message->getHeaderLines('Authorization');
        $this->assertIsArray($lines);
        $this->assertCount(1, $lines);
        $this->assertSame('Basic john:1234', $lines[0]);
        $values = $this->message->getHeaderAsList('Authorization');
        $this->assertIsArray($values);
        $this->assertCount(1, $values);
        $this->assertSame('Basic john:1234', $values[0]);
        // Accept-Language
        $this->assertSame(
            'en-US, en',
            $this->message->getHeaderLine('Accept-Language'),
        );
        $lines = $this->message->getHeaderLines('Accept-Language');
        $this->assertIsArray($lines);
        $this->assertCount(1, $lines);
        $this->assertSame('en-US, en', $lines[0]);
        $values = $this->message->getHeaderAsList('Accept-Language');
        $this->assertIsArray($values);
        $this->assertCount(2, $values);
        $this->assertSame('en-US', $values[0]);
        $this->assertSame('en', $values[1]);
    }

    /**
     * @covers ::getBody
     * @covers ::setBody
     * @uses Laucov\Files\Resource\StringSource::__construct
     * @uses Laucov\Files\Resource\StringSource::read
     */
    public function testCanSetBody(): void
    {
        // Set the body.
        $this->message->setBody('Lorem ipsum');

        // Get the body.
        /** @var StringSource */
        $body = $this->message->getBody();
        $this->assertInstanceOf(StringSource::class, $body);

        // Test body reading.
        $this->assertSame('Lorem', $body->read(5));
        $this->assertSame(' ipsum', $body->read(6));
        $this->assertSame('', $body->read(2));

        // Test stringifying the body.
        $this->assertSame('Lorem ipsum', (string) $body);
    }

    /**
     * @covers ::getProtocolVersion
     * @covers ::setProtocolVersion
     */
    public function testCanSetProtocolVersion(): void
    {
        $this->assertNull($this->message->getProtocolVersion());
        $this->message->setProtocolVersion('1.1');
        $this->assertSame('1.1', $this->message->getProtocolVersion());

        $this->expectException(\InvalidArgumentException::class);
        $this->message->setProtocolVersion('1.9');
    }

    /**
     * @coversNothing
     */
    public function testTrimsValues(): void
    {
        // Test ::setHeaderLine trimming.
        $this->message->setHeaderLine('Content-Length', " 20 \n\n   \t");
        $this->assertSame(
            '20',
            $this->message->getHeaderLine('Content-Length'),
        );

        // Test ::addHeaderLine trimming.
        $this->message->addHeaderLine('Set-Cookie', "\nfoo=bar ");
        $this->assertSame(
            'foo=bar',
            $this->message->getHeaderLine('Set-Cookie'),
        );

        // Test ::addHeaderValue trimming.
        $this->message->addHeaderValue('Cache-Control', "\n\n\n\r no-cache");
        $this->message->addHeaderValue('Cache-Control', "   no-store ");
        $this->assertSame(
            'no-cache, no-store',
            $this->message->getHeaderLine('Cache-Control'),
        );
    }
}
