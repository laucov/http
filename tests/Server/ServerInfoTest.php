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

namespace Tests\Server;

use Laucov\Http\Server\ServerInfo;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Http\Server\ServerInfo
 */
class ServerInfoTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::getProtocolName
     * @covers ::getProtocolVersion
     * @covers ::getRequestUri
     */
    public function testCanGetInfo(): void
    {
        // Create instance.
        $info_a = new ServerInfo([
            'DOCUMENT_ROOT' => '/www/foo.com',
            'REMOTE_ADDR' => '::1',
            'REMOTE_PORT' => '35020',
            'SERVER_SOFTWARE' => 'PHP 8.9.0 Development Server',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '8888',
            'REQUEST_URI' => '/foobar/baz?page=3',
            'REQUEST_METHOD' => 'GET',
            'SCRIPT_NAME' => '/index.php',
            'SCRIPT_FILENAME' => '/www/foo.com/index.php',
            'PATH_INFO' => '/foobar/baz',
            'PHP_SELF' => '/index.php/foobar/baz',
            'QUERY_STRING' => 'page=3',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'localhost:8888',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0',
            'HTTP_ACCEPT' => 'application/json,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_ACCEPT_LANGUAGE' => 'es-MX,es;q=0.9,en-US;q=0.7,en;q=0.5',
            'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
            'HTTP_CONNECTION' => 'keep-alive',
            'HTTP_COOKIE' => 'PHPSESSID=dGFuYWhvcmFkZW1vbGhhcm9iaXNjb2l0bw',
            'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
            'HTTP_SEC_FETCH_DEST' => 'document',
            'HTTP_SEC_FETCH_MODE' => 'navigate',
            'HTTP_SEC_FETCH_SITE' => 'none',
            'HTTP_SEC_FETCH_USER' => '?1',
            'REQUEST_TIME_FLOAT' => 123654789.987123,
            'REQUEST_TIME' => 123654789,
        ]);

        // Get values.
        $this->assertSame(123654789, $info_a->get('REQUEST_TIME'));
        $this->assertNull($info_a->get('REQUEST_FOOBAR'));
        $this->assertSame('nothing', $info_a->get('REQUEST_FOOBAR', 'nothing'));

        // Create empty server info to test other methods.
        $info_b = new ServerInfo([]);

        // Get full URI.
        $uri_a = $info_a->getRequestUri();
        $this->assertSame('https', $uri_a->scheme);
        $this->assertSame('', $uri_a->userInfo);
        $this->assertSame('localhost', $uri_a->host);
        $this->assertSame(8888, $uri_a->port);
        $this->assertSame('foobar/baz', $uri_a->path);
        $this->assertSame('page=3', $uri_a->query);
        $this->assertSame('', $uri_a->fragment);
        $uri_b = $info_b->getRequestUri();
        $this->assertSame('', $uri_b->scheme);
        $this->assertSame('', $uri_b->userInfo);
        $this->assertSame('', $uri_b->host);
        $this->assertNull($uri_b->port);
        $this->assertSame('', $uri_b->path);
        $this->assertSame('', $uri_b->query);
        $this->assertSame('', $uri_b->fragment);

        // Get HTTP protocol information.
        $this->assertSame('HTTP', $info_a->getProtocolName());
        $this->assertSame('1.1', $info_a->getProtocolVersion());
        $this->assertNull($info_b->getProtocolName());
        $this->assertNull($info_b->getProtocolVersion());
    }
}
