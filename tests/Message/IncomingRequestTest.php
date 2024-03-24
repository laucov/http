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

use Laucov\Files\Resource\Uri;
use Laucov\Http\Cookie\RequestCookie;
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
     * @uses Laucov\Http\Cookie\AbstractCookie::__construct
     * @uses Laucov\Http\Message\AbstractIncomingMessage::__construct
     * @uses Laucov\Http\Message\AbstractMessage::getBody
     * @uses Laucov\Http\Message\IncomingRequest::__construct
     * @uses Laucov\Files\Resource\Uri::__construct
     * @uses Laucov\Files\Resource\Uri::fromString
     */
    public function testCanInstantiateWithPostOrTextOrResource(): void
    {
        // Instantiante with POST variables.
        $request = new IncomingRequest(
            content_or_post: [
                'name' => 'John',
                'age' => '32',
                'fruits' => ['apple', 'tomato'],
            ],
        );

        // Check that there is no body.
        $this->assertSame('', (string) $request->getBody());
        // Get variable.
        $post = $request->getPostVariables();
        $this->assertSame('John', $post->getValue('name'));
        $this->assertSame('32', $post->getValue('age'));
        $this->assertSame('apple', $post->getValue(['fruits', 0]));
        $this->assertSame('tomato', $post->getValue(['fruits', 1]));

        // Instantiate with text content.
        $request = new IncomingRequest('Text content.');
        $this->assertSame('Text content.', (string) $request->getBody());

        // Instantiate with file content.
        $file = fopen('data://text/plain,File content.', 'r');
        $request = new IncomingRequest($file);
        $this->assertSame('File content.', (string) $request->getBody());
    }

    /**
     * @covers ::__construct
     * @uses Laucov\Http\Message\AbstractIncomingMessage::__construct
     * @uses Laucov\Http\Message\Traits\RequestTrait::getUri
     */
    public function testCanInstantiateWithTextUriOrUriObject(): void
    {
        // Create URI options.
        $text = 'http://foobar.com/a/b/c';
        $object = new Uri('http', host: 'foobar.com', path: 'a/b/c');

        // Create requests.
        $requests = [
            new IncomingRequest('', uri: $text),
            new IncomingRequest('', uri: $object),
        ];

        // Test instances.
        foreach ($requests as $request) {
            $uri = $request->getUri();
            $this->assertSame('http', $uri->scheme);
            $this->assertSame('foobar.com', $uri->host);
            $this->assertSame('a/b/c', $uri->path);
        }
    }

    /**
     * @covers ::__construct
     * @covers ::getParameters
     * @uses Laucov\Arrays\ArrayReader::__construct
     * @uses Laucov\Arrays\ArrayReader::getValue
     * @uses Laucov\Files\Resource\StringSource::__construct
     * @uses Laucov\Http\Cookie\AbstractCookie::__construct
     * @uses Laucov\Http\Message\AbstractIncomingMessage::__construct
     * @uses Laucov\Http\Message\AbstractMessage::getHeaderLine
     * @uses Laucov\Http\Message\AbstractMessage::getProtocolVersion
     * @uses Laucov\Http\Message\IncomingRequest::__construct
     * @uses Laucov\Http\Message\Traits\RequestTrait::getCookie
     * @uses Laucov\Http\Message\Traits\RequestTrait::getCookieNames
     * @uses Laucov\Http\Message\Traits\RequestTrait::getMethod
     * @uses Laucov\Http\Message\Traits\RequestTrait::getUri
     * @uses Laucov\Files\Resource\Uri::__construct
     * @uses Laucov\Files\Resource\Uri::fromString
     */
    public function testSetsPropertiesFromConstructor(): void
    {
        // Create request.
        $request = new IncomingRequest(
            content_or_post: '',
            headers: [
                'Content-Type' => 'application/json',
            ],
            protocol_version: '1.0',
            method: 'POST',
            uri: 'http://foobar.com/path/to/somewhere',
            parameters: [
                'search' => 'foobar',
                'page' => '2',
            ],
            cookies: [
                'dark-mode' => 'false',
                'name' => 'John',
            ],
        );

        // Test headers.
        $this->assertSame(
            'application/json',
            $request->getHeaderLine('Content-Type'),
        );

        // Test protocol version.
        $protocol_version = $request->getProtocolVersion();
        $this->assertSame('1.0', $protocol_version);

        // Test method.
        $method = $request->getMethod();
        $this->assertSame('POST', $method);

        // Test URI.
        $uri = $request->getUri();
        $this->assertSame('http', $uri->scheme);
        $this->assertSame('foobar.com', $uri->host);
        $this->assertSame('path/to/somewhere', $uri->path);

        // Test parameters.
        $parameters = $request->getParameters();
        $this->assertSame('foobar', $parameters->getValue('search'));
        $this->assertSame('2', $parameters->getValue('page'));

        // Test cookies.
        $cookie = $request->getCookie('dark-mode');
        $this->assertInstanceOf(RequestCookie::class, $cookie);
        $this->assertSame('dark-mode', $cookie->name);
        $this->assertSame('false', $cookie->value);
        $cookie_names = $request->getCookieNames();
        $this->assertIsArray($cookie_names);
        $this->assertCount(2, $cookie_names);
        $this->assertSame('dark-mode', $cookie_names[0]);
        $this->assertSame('name', $cookie_names[1]);
    }


    /**
     * @covers ::__construct
     * @uses Laucov\Http\Cookie\AbstractCookie::__construct
     * @uses Laucov\Http\Message\AbstractIncomingMessage::__construct
     */
    public function testCookieValuesMustBeStrings(): void
    {
        new IncomingRequest([], [], null, 'GET', '', [], [
            'cookie-a' => 'value-a',
        ]);
        $this->expectException(\InvalidArgumentException::class);
        new IncomingRequest([], [], null, 'GET', '', [], [
            'cookie-a' => new \stdClass(),
        ]);
    }
}
