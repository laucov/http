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

use Laucov\Http\Message\AbstractMessage;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Http\Message\AbstractMessage
 */
final class AbstractMessageTest extends TestCase
{
    private AbstractMessage $message;

    protected function setUp(): void
    {
        $class_name = AbstractMessage::class;
        $this->message = $this->getMockForAbstractClass($class_name);
    }

    /**
     * @covers ::getHeader
     * @covers ::getHeaderAsList
     * @covers ::getHeaderNames
     */
    public function testCanGetHeader(): void
    {
        $this->assertNull($this->message->getHeader('Content-Type'));
        $this->assertNull($this->message->getHeaderAsList('Cache-Control'));
        $header_names = $this->message->getHeaderNames();
        $this->assertIsArray($header_names);
        $this->assertCount(0, $header_names);
    }

    /**
     * @covers ::getBody
     */
    public function testCanGetBody(): void
    {
        $this->assertNull($this->message->getBody());
    }

    /**
     * @covers ::getProtocolVersion
     */
    public function testCanGetProtocolVersion(): void
    {
        $this->assertNull($this->message->getProtocolVersion());
    }
}
