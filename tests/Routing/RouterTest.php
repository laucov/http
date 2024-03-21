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

namespace Tests\Routing;

use Laucov\Http\Message\IncomingRequest;
use Laucov\Http\Message\OutgoingResponse;
use Laucov\Http\Message\RequestInterface;
use Laucov\Http\Message\ResponseInterface;
use Laucov\Http\Routing\Call\Interfaces\PreludeInterface;
use Laucov\Http\Routing\Call\Route;
use Laucov\Http\Routing\Router;
use Laucov\Http\Server\ServerInfo;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Http\Routing\Router
 * @todo Test PreludeInterface class name requirement.
 * @todo Test invalid callbacks.
 */
class RouterTest extends TestCase
{
    protected Router $router;

    /**
     * @covers ::__construct
     * @covers ::findRoute
     * @covers ::getRouteKeys
     * @covers ::popPrefix
     * @covers ::pushPrefix
     * @covers ::setCallableRoute
     * @covers ::setClassRoute
     * @covers ::setPattern
     * @covers ::validateCallback
     * @covers ::validateReturnType
     * @uses Laucov\Http\Message\AbstractIncomingMessage::__construct
     * @uses Laucov\Http\Message\AbstractMessage::getBody
     * @uses Laucov\Http\Message\AbstractOutgoingMessage::setBody
     * @uses Laucov\Http\Message\IncomingRequest::__construct
     * @uses Laucov\Http\Message\Traits\RequestTrait::getMethod
     * @uses Laucov\Http\Message\Traits\RequestTrait::getUri
     * @uses Laucov\Http\Routing\Call\Callback::__construct
     * @uses Laucov\Http\Routing\Call\Route::__construct
     * @uses Laucov\Http\Routing\Call\Route::createResponse
     * @uses Laucov\Http\Routing\Call\Route::run
     * @uses Laucov\Http\Server\ServerInfo::__construct
     */
    public function testCanCaptureSegments(): void
    {
        // Set closures.
        $closures = [
            fn (): string => 'Hello, World!',
            fn (): string => 'Hello, Everyone!',
            fn (): string => 'Hello, Planet!',
            fn (): string => 'Hello, Universe!',
            fn (string $a): string => "Value: {$a}",
            fn (string ...$a): string => implode(', ', $a),
            fn (): string => 'No parameters!',
            fn (): string => 'Animals',
            fn (): string => 'Lizard',
            fn (): string => 'Ant',
            fn (): string => 'Dog',
            fn (): string => 'Elephant',
            fn (): string => 'Shark',
            fn (): string => 'Apple',
            fn (): string => 'Ice cream',
            fn (): string => 'Home',
        ];

        // Set routes.
        $this->router
            ->setCallableRoute('GET', 'path/to/route-a', $closures[0])
            ->setCallableRoute('POST', 'path/to/route-b/', $closures[1])
            ->setCallableRoute('PUT', '/path/to/route-c', $closures[2])
            ->setCallableRoute('PATCH', '/path/to/route-d/', $closures[3])
            ->setPattern('int', '/^[0-9]+$/')
            ->setPattern('alpha', '/^[A-Za-z]+$/')
            ->setCallableRoute('GET', 'routes/:alpha', $closures[4])
            ->setCallableRoute('GET', ':alpha/:alpha/:int', $closures[5])
            ->setCallableRoute('GET', ':alpha/:alpha', $closures[6])
            ->pushPrefix('animals')
                ->setCallableRoute('GET', '', $closures[7])
                ->setCallableRoute('GET', 'lizard', $closures[8])
                ->setCallableRoute('GET', 'ant', $closures[9])
                ->pushPrefix('mammals/')
                    ->setCallableRoute('GET', 'dog', $closures[10])
                    ->setCallableRoute('GET', 'elephant', $closures[11])
                ->popPrefix()
                ->setCallableRoute('GET', 'shark', $closures[12])
            ->popPrefix()
            ->pushPrefix('/food/fruits')
                ->setCallableRoute('GET', 'apple', $closures[13])
                ->popPrefix()
                ->pushPrefix('/food/candy/')
                    ->setCallableRoute('GET', 'ice-cream', $closures[14])
                ->popPrefix()
            ->popPrefix()
            ->setCallableRoute('GET', '', $closures[15])
            ->pushPrefix('classes/x')
                ->setClassRoute('GET', 'hi', X::class, 'greet', 'Hi', 'John');
        
        // Set tests and expectations.
        $tests = [
            // Assert that route A exists.
            ['GET', 'path/to/route-a', 'Hello, World!'],
            // Assert that route A only exists for GET requests.
            ['POST', 'path/to/route-a', null],
            // Assert that route Z does not exist.
            ['GET', 'path/to/route-z', null],
            // Assert that intermediary paths of route A cannot be accessed.
            ['GET', 'path/to', null],
            // Assert that route B exists - test right trimming.
            ['POST', 'path/to/route-b', 'Hello, Everyone!'],
            // Assert that route C exists - test left trimming.
            ['PUT', 'path/to/route-c', 'Hello, Planet!'],
            // Assert that route D exists - test full trimming.
            ['PATCH', 'path/to/route-d', 'Hello, Universe!'],
            // Assert that can capture a segment.
            ['GET', 'routes/foobar', 'Value: foobar'],
            // Assert that can capture multiple segments.
            ['GET', 'foo/bar/15', 'foo, bar, 15'],
            // Assert that captured segments are of optional use.
            ['GET', 'foo/bar', 'No parameters!'],
            // Push prefixes.
            ['GET', 'animals', 'Animals'],
            ['GET', 'animals/lizard', 'Lizard'],
            ['GET', 'animals/ant', 'Ant'],
            // Push prefixes - right trimming.
            ['GET', 'animals/mammals/dog', 'Dog'],
            ['GET', 'animals/mammals/elephant', 'Elephant'],
            // Pop prefix.
            ['GET', 'animals/shark', 'Shark'],
            // Pop and push prefix - left trimming.
            ['GET', 'food/fruits/apple', 'Apple'],
            // Pop and push prefix - full trimming.
            ['GET', 'food/candy/ice-cream', 'Ice cream'],
            // Test root path.
            ['GET', '', 'Home'],
            // Test class routes.
            ['GET', 'classes/x/hi', 'Hi, John!'],
        ];

        // Run tests.
        foreach ($tests as [$method, $path, $expected]) {
            $this->assertResponse($method, $path, $expected);
        }
    }

    /**
     * @covers ::addPrelude
     * @covers ::findRoute
     * @covers ::setPreludes
     * @uses Laucov\Http\Message\AbstractIncomingMessage::__construct
     * @uses Laucov\Http\Message\AbstractMessage::getBody
     * @uses Laucov\Http\Message\AbstractOutgoingMessage::setBody
     * @uses Laucov\Http\Message\IncomingRequest::__construct
     * @uses Laucov\Http\Message\Traits\RequestTrait::getMethod
     * @uses Laucov\Http\Message\Traits\RequestTrait::getUri
     * @uses Laucov\Http\Routing\AbstractRouteCallable::getPreludeNames
     * @uses Laucov\Http\Routing\AbstractRouteCallable::setPreludeNames
     * @uses Laucov\Http\Routing\AbstractRouteCallable::validate
     * @uses Laucov\Http\Routing\AbstractRouteCallable::validateParameterTypes
     * @uses Laucov\Http\Routing\AbstractRouteCallable::validateReturnType
     * @uses Laucov\Http\Routing\AbstractRoutePrelude::__construct
     * @uses Laucov\Http\Routing\Call\Callback::__construct
     * @uses Laucov\Http\Routing\Call\Route::__construct
     * @uses Laucov\Http\Routing\Call\Route::createResponse
     * @uses Laucov\Http\Routing\Call\Route::run
     * @uses Laucov\Http\Routing\Router::__construct
     * @uses Laucov\Http\Routing\Router::getRouteKeys
     * @uses Laucov\Http\Routing\Router::setCallableRoute
     * @uses Laucov\Http\Routing\Router::validateCallback
     * @uses Laucov\Http\Routing\Router::validateReturnType
     * @uses Laucov\Http\Server\ServerInfo::__construct
     */
    public function testCanSetAndUsePreludes(): void
    {
        // Add prelude and appendix options.
        $this->router
            ->addPrelude('p1', PreludeA::class, [])
            ->addPrelude('p2', PreludeA::class, ['Hello, Universe!'])
            ->addPrelude('p3', PreludeB::class, []);
        
        // Create closures.
        $closure = fn (): string => 'Hello, World!';
        
        // Set routes.
        $this->router
            ->setPreludes('p1')
                ->setCallableRoute('GET', 'route-a', $closure)
            ->setPreludes()
                ->setCallableRoute('GET', 'route-b', $closure)
            ->setPreludes('p2')
                ->setCallableRoute('GET', 'route-c', $closure)
            ->setPreludes('p1', 'p3')
                ->setCallableRoute('GET', 'route-d', $closure)
                ->setCallableRoute('POST', 'route-d', $closure);
        
        // Test routes.
        $tests = [
            // Test non-interrupting preludes.
            ['GET', 'route-a', 'Hello, World!'],
            // Test removing all preludes.
            ['GET', 'route-b', 'Hello, World!'],
            // Test prelude with parameter that will interrupt the request.
            ['GET', 'route-c', 'Hello, Universe!'],
            // Test with prelude that interrupts POST requests.
            ['GET', 'route-d', 'Hello, World!'],
            ['POST', 'route-d', 'Interrupted a POST!'],
        ];
        foreach ($tests as [$method, $path, $expected]) {
            $this->assertResponse($method, $path, $expected);
        }
    }

    /**
     * @covers ::findRoute
     * @uses Laucov\Http\Message\AbstractIncomingMessage::__construct
     * @uses Laucov\Http\Message\AbstractMessage::getBody
     * @uses Laucov\Http\Message\AbstractOutgoingMessage::setBody
     * @uses Laucov\Http\Message\IncomingRequest::__construct
     * @uses Laucov\Http\Message\Traits\RequestTrait::getMethod
     * @uses Laucov\Http\Message\Traits\RequestTrait::getUri
     * @uses Laucov\Http\Routing\Call\Callback::__construct
     * @uses Laucov\Http\Routing\Call\Route::__construct
     * @uses Laucov\Http\Routing\Call\Route::createResponse
     * @uses Laucov\Http\Routing\Call\Route::run
     * @uses Laucov\Http\Routing\Router::__construct
     * @uses Laucov\Http\Routing\Router::getRouteKeys
     * @uses Laucov\Http\Routing\Router::pushPrefix
     * @uses Laucov\Http\Routing\Router::setCallableRoute
     * @uses Laucov\Http\Routing\Router::setClassRoute
     * @uses Laucov\Http\Routing\Router::setPattern
     * @uses Laucov\Http\Routing\Router::validateCallback
     * @uses Laucov\Http\Routing\Router::validateReturnType
     * @uses Laucov\Http\Server\ServerInfo::__construct
     * @uses Laucov\Http\Server\ServerInfo::get
     * @uses Laucov\Http\Server\ServerInfo::getProtocolVersion
     */
    public function testCanInjectRequestAndServerInfo(): void
    {
        // Set server info.
        $server = new ServerInfo([
            'SERVER_PROTOCOL' => 'HTTP/1.1',
        ]);

        // Set closures.
        $callable = function (
            string $a,
            RequestInterface $request,
            ServerInfo $server,
            string ...$b,
        ): string {
            $host = $request->getUri()->host;
            $version = $server->getProtocolVersion() ?: '???';
            return "{$a}, {$host}, {$version}, " . implode(', ', $b);
        };

        // Set routes.
        $this->router
            ->setPattern('any', '/^.+$/')
            ->setCallableRoute('GET', 'server/:any/:any/:any', $callable)
            ->pushPrefix('classes')
                ->setClassRoute('GET', 'y/method', Y::class, 'getMethod')
                ->pushPrefix('y')
                    ->setClassRoute('POST', 'method', Y::class, 'getMethod')
                    ->setClassRoute('GET', 'protocol', Y::class, 'getProtocol');
        
        // Set tests.
        $tests = [
            ['GET', 'server/x/y/z', $server, 'x, foobar.com, 1.1, y, z'],
            ['GET', 'server/9/8/7', null, '9, foobar.com, ???, 8, 7'],
            ['GET', 'classes/y/method', null, 'GET'],
            ['POST', 'classes/y/method', null, 'POST'],
            ['GET', 'classes/y/protocol', null, 'unknown'],
            ['GET', 'classes/y/protocol', $server, '1.1'],
        ];

        // Run tests.
        foreach ($tests as [$method, $path, $srv, $expected]) {
            $this->assertResponse($method, $path, $expected, $srv);
        }
    }

    /**
     * @covers ::addPrelude
     * @uses Laucov\Http\Routing\Router::__construct
     */
    public function testPreludesMustImplementThePreludeInterface(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->router->addPrelude('foo', NotAPrelude::class, []);
    }

    /**
     * Assert that a route is found for the given `$method` and `$path`.
     * 
     * Also assert that the route response contains `$expected` as its content.
     */
    protected function assertResponse(
        string $method,
        string $path,
        null|string $expected,
        null|ServerInfo $server_info = null,
    ): void {
        // Create request.
        $uri = "https://foobar.com/" . $path;
        $request = new IncomingRequest('', method: $method, uri: $uri);

        // Get route.
        $route = $this->router->findRoute($request, $server_info);

        // Set message and test.
        $message = "Test {$method} {$uri}";
        if ($expected === null) {
            $this->assertNull($route, $message);
        } else {
            $this->assertInstanceOf(Route::class, $route, $message);
            $response = $route->run();
            $content = (string) $response->getBody();
            $this->assertSame($expected, $content, $message);
        }
    }

    protected function setUp(): void
    {
        $this->router = new Router();
    }
}

class NotAPrelude
{
    public function run(): null|string
    {
        return null;
    }
}

class PreludeA implements PreludeInterface
{
    public static int $run_count = 0;

    public function __construct(protected array $parameters)
    {
    }

    public function run(): null|string
    {
        static::$run_count++;

        if ($this->parameters[0] ?? null) {
            return $this->parameters[0];
        }

        return null;
    }
}

class PreludeB implements PreludeInterface
{
    public static int $run_count = 0;

    public function __construct(
        protected RequestInterface $request,
        protected ServerInfo $server,
    ) {}

    public function run(): null|ResponseInterface
    {
        if ($this->request->getMethod() === 'POST') {
            $response = new OutgoingResponse();
            $response->setBody('Interrupted a POST!');
            return $response;
        }

        static::$run_count++;
        return null;
    }
}

class X
{
    public function __construct(
        protected string $greeting,
        protected string $name,
    ) {}

    public function greet(): string
    {
        return "{$this->greeting}, {$this->name}!";
    }
}

class Y
{
    public function getMethod(RequestInterface $request): ResponseInterface
    {
        $response = new OutgoingResponse();
        $response->setBody($request->getMethod());
        return $response;
    }

    public function getProtocol(ServerInfo $server): string
    {
        return $server->getProtocolVersion() ?? 'unknown';
    }
}
