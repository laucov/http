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

use Laucov\Http\Message\IncomingRequest;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Http\Message\IncomingRequest
 */
class IncomingRequestTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getPostVariables
     * @uses Laucov\Arrays\ArrayReader::__construct
     * @uses Laucov\Arrays\ArrayReader::getValue
     * @uses Laucov\Arrays\ArrayReader::validateKeys
     * @uses Laucov\Files\Resource\StringSource::__construct
     * @uses Laucov\Files\Resource\StringSource::read
     * @uses Laucov\Http\Message\AbstractIncomingMessage::__construct
     * @uses Laucov\Http\Message\AbstractMessage::getBody
     * @uses Laucov\Http\Message\IncomingRequest::__construct
     * @uses Laucov\Files\Resource\Uri::__construct
     * @uses Laucov\Files\Resource\Uri::fromString
     */
    public function testCanInstantiateWithPostVariablesArray(): void
    {
        $request = $this->getInstance([
            'name' => 'John',
            'age' => '32',
            'fruits' => ['apple', 'tomato'],
        ]);

        $this->assertSame('', $request->getBody()->read(10));

        $fruit = $request->getPostVariables()->getValue(['fruits', 1]);
        $this->assertSame('tomato', $fruit);
    }

    /**
     * @covers ::__construct
     * @uses Laucov\Arrays\ArrayReader::__construct
     * @uses Laucov\Files\Resource\StringSource::__construct
     * @uses Laucov\Files\Resource\StringSource::read
     * @uses Laucov\Http\Message\AbstractIncomingMessage::__construct
     * @uses Laucov\Http\Message\AbstractMessage::getBody
     * @uses Laucov\Http\Message\IncomingRequest::__construct
     * @uses Laucov\Files\Resource\Uri::__construct
     * @uses Laucov\Files\Resource\Uri::fromString
     */
    public function testCanInstantiateWithTextAndFile(): void
    {
        $text_request = $this->getInstance('Text content.');
        $text = $text_request->getBody()->read(13);
        $this->assertSame('Text content.', $text);

        $file = fopen('data://text/plain,File content.', 'r');
        $file_request = $this->getInstance($file);
        $text = $file_request->getBody()->read(13);
        $this->assertSame('File content.', $text);
    }

    /**
     * @covers ::__construct
     * @covers ::getParameters
     * @uses Laucov\Arrays\ArrayReader::__construct
     * @uses Laucov\Arrays\ArrayReader::getValue
     * @uses Laucov\Files\Resource\StringSource::__construct
     * @uses Laucov\Http\Message\AbstractIncomingMessage::__construct
     * @uses Laucov\Http\Message\AbstractMessage::getHeader
     * @uses Laucov\Http\Message\AbstractMessage::getProtocolVersion
     * @uses Laucov\Http\Message\IncomingRequest::__construct
     * @uses Laucov\Http\Message\Traits\RequestTrait::getMethod
     * @uses Laucov\Http\Message\Traits\RequestTrait::getUri
     * @uses Laucov\Files\Resource\Uri::__construct
     * @uses Laucov\Files\Resource\Uri::fromString
     */
    public function testConstructorSavesInformation(): void
    {
        $request = $this->getInstance('Any content.');

        $protocol_version = $request->getProtocolVersion();
        $this->assertSame('1.0', $protocol_version);

        $method = $request->getMethod();
        $this->assertSame('POST', $method);

        $uri = $request->getUri();
        $this->assertSame('http', $uri->scheme);
        $this->assertSame('foobar.com', $uri->host);

        $parameter = $request->getParameters()->getValue('search');
        $this->assertSame('foobar', $parameter);

        $header = $request->getHeader('Authorization');
        $this->assertSame('Basic john.doe:1234', $header);
    }

    /**
     * Get a request pre-configured instance.
     */
    public function getInstance(mixed $content): IncomingRequest
    {
        // Create headers.
        $headers = [
            'Authorization' => 'Basic john.doe:1234',
        ];

        // Create parameters.
        $parameters = [
            'page' => '2',
            'search' => 'foobar',
        ];

        // Create request.
        return new IncomingRequest(
            content_or_post: $content,
            headers: $headers,
            protocol_version: '1.0',
            method: 'POST',
            uri: 'http://foobar.com/hello-world',
            parameters: $parameters,
        );
    }
}
