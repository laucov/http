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
     * @covers ::addHeader
     * @covers ::getHeader
     * @covers ::getHeaderAsList
     * @uses Laucov\Http\Message\AbstractOutgoingMessage::setHeader
     */
    public function testCanAddHeaders(): void
    {
        $this->message->addHeader('Cache-Control', 'must-understand');
        $this->message->addHeader('Cache-Control', 'no-store');

        $line = $this->message->getHeader('Cache-Control');
        $this->assertSame('must-understand, no-store', $line);

        $list = $this->message->getHeaderAsList('Cache-Control');
        $this->assertCount(2, $list);
        $this->assertContains('must-understand', $list);
        $this->assertContains('no-store', $list);
    }

    /**
     * @covers ::getBody
     * @covers ::setBody
     * @uses Laucov\Files\Resource\StringSource::__construct
     * @uses Laucov\Files\Resource\StringSource::read
     */
    public function testCanSetBody(): void
    {
        $this->message->setBody('Lorem ipsum');
        /** @var StringSource */
        $body = $this->message->getBody();
        $this->assertInstanceOf(StringSource::class, $body);
        $this->assertSame('Lorem ipsum', $body->read(11));
    }

    /**
     * @covers ::getHeaderNames
     * @covers ::setHeader
     * @uses Laucov\Http\Message\AbstractMessage::getHeader
     */
    public function testCanSetHeader(): void
    {
        $this->message->setHeader('Content-Length', '10');
        $this->assertSame('10', $this->message->getHeader('Content-Length'));
        $this->message->setHeader('Content-Type', 'application/json');

        $header_names = $this->message->getHeaderNames();
        $this->assertIsArray($header_names);
        $this->assertCount(2, $header_names);
        $this->assertSame('Content-Length', $header_names[0]);
        $this->assertSame('Content-Type', $header_names[1]);
    }

    /**
     * @covers ::setProtocolVersion
     * @uses Laucov\Http\Message\AbstractMessage::getProtocolVersion
     */
    public function testCanSetProtocolVersion(): void
    {
        $this->message->setProtocolVersion('1.1');
        $this->assertSame('1.1', $this->message->getProtocolVersion());

        $this->expectException(\InvalidArgumentException::class);
        $this->message->setProtocolVersion('1.9');
    }

    /**
     * @covers ::addHeader
     * @covers ::setHeader
     * @uses Laucov\Http\Message\AbstractMessage::getHeader
     * @uses Laucov\Http\Message\AbstractMessage::getHeaderAsList
     */
    public function testFiltersValues(): void
    {
        $this->message->setHeader('Content-Length', " 20 \n\n   \t");
        $this->assertSame('20', $this->message->getHeader('Content-Length'));

        $this->message->addHeader('Cache-Control', "\n\n\n\r must-understand");
        $this->message->addHeader('Cache-Control', "   no-store ");

        $line = $this->message->getHeader('Cache-Control');
        $this->assertSame('must-understand, no-store', $line);

        $list = $this->message->getHeaderAsList('Cache-Control');
        $this->assertCount(2, $list);
        $this->assertContains('must-understand', $list);
        $this->assertContains('no-store', $list);
    }
}
