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
use Laucov\Http\Message\RequestInterface;
use Laucov\Http\Message\ResponseInterface;
use Laucov\Http\Routing\AbstractRoutePrelude;
use Laucov\Http\Routing\Call\Route;
use Laucov\Http\Routing\Router;
use Laucov\Http\Server\ServerInfo;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Http\Routing\Router
 * @todo Test preludes with other constructor dependencies.
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
     * @uses Laucov\Arrays\ArrayBuilder::setValue
     * @uses Laucov\Arrays\ArrayReader::__construct
     * @uses Laucov\Arrays\ArrayReader::getValue
     * @uses Laucov\Arrays\ArrayReader::validateKeys
     * @uses Laucov\Files\Resource\StringSource::__construct
     * @uses Laucov\Files\Resource\StringSource::__toString
     * @uses Laucov\Files\Resource\Uri::__construct
     * @uses Laucov\Files\Resource\Uri::fromString
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
     * @uses Laucov\Http\Routing\Call\Callback::__construct
     * @uses Laucov\Http\Routing\Call\Route::__construct
     * @uses Laucov\Http\Routing\Call\Route::createResponse
     * @uses Laucov\Http\Routing\Call\Route::run
     * @uses Laucov\Http\Routing\Router::__construct
     * @uses Laucov\Http\Server\ServerInfo::__construct
     * @uses Laucov\Http\Server\ServerInfo::get
     */
    public function testCanSetAndFindRoutes(): void
    {
        // Set closures.
        $closures = [
            fn (): string => 'Hello, World!',
            fn (): string => 'Hello, Everyone!',
            fn (): string => 'Hello, Planet!',
            fn (): string => 'Hello, Universe!',
        ];

        // Set routes.
        $this->router
            ->setCallableRoute('GET', 'path/to/route-a', $closures[0])
            ->setCallableRoute('POST', 'path/to/route-b/', $closures[1])
            ->setCallableRoute('POST', '/path/to/route-c', $closures[2])
            ->setCallableRoute('POST', '/path/to/route-d/', $closures[3]);
        
        // Set tests and expectations.
        $tests = [
            // Assert route A exists.
            ['GET', 'path/to/route-a', 'Hello, World!'],
            // Assert route A only exists for GET requests.
            ['POST', 'path/to/route-a', null],
            // Assert route Z does not exist.
            ['GET', 'path/to/route-z', null],
            // Assert that intermediary paths of route A cannot be accessed.
            ['GET', 'path/to', null],
            // Assert route B exists - test right trimming.
            ['POST', 'path/to/route-b', 'Hello, Everyone!'],
            // Assert route C exists - test left trimming.
            ['POST', 'path/to/route-c', 'Hello, Planet!'],
            // Assert route D exists - test full trimming.
            ['POST', 'path/to/route-d', 'Hello, Universe!'],
        ];

        // Run tests.
        foreach ($tests as $i => [$method, $path, $expected]) {
            $uri = "https://foobar.com/" . $path;
            $name = "Assert that {$method} {$uri}";
            $request = new IncomingRequest('', method: $method, uri: $uri);
            $route = $this->router->findRoute($request);
            if ($expected === null) {
                $message = "{$name} is null";
                $this->assertNull($route, "{$name} is null");
            } else {
                $message = "{$name} is a route";
                $this->assertInstanceOf(Route::class, $route, $message);
                $response = $route->run();
                $content = (string) $response->getBody();
                $message = "{$name} output";
                $this->assertSame($expected, $content, "{$name}");
            }
        }

        return;

        // Test router's path trimming.
        $route_b = $this->findRoute('POST', 'path/to/route-b');
        $this->assertInstanceOf(Route::class, $route_b);
        $this->assertSame('Output B', (string) $route_b->run()->getBody());
        $closure_c = fn (): string => 'Output C';
        $this->router->setCallableRoute('PUT', '/path/to/route-c', $closure_c);
        $route_c = $this->findRoute('PUT', 'path/to/route-c');
        $this->assertInstanceOf(Route::class, $route_c);
        $this->assertSame('Output C', (string) $route_c->run()->getBody());
        $closure_d = fn (): string => 'Output D';
        $this->router->setCallableRoute('PATCH', '/path/to/route-d/', $closure_d);
        $route_d = $this->findRoute('PATCH', 'path/to/route-d');
        $this->assertInstanceOf(Route::class, $route_d);
        $this->assertSame('Output D', (string) $route_d->run()->getBody());

        // Set patterns.
        $this->assertSame(
            $this->router,
            $this->router->setPattern('int', '/^[0-9]+$/'),
        );
        $this->router->setPattern('alpha', '/^[A-Za-z]+$/');

        // Test without parameters.
        $closure_e = fn (): string => 'Output E';
        $this->router->setCallableRoute('POST', 'routes/:alpha', $closure_e);
        $route_e = $this->findRoute('POST', 'routes/e');
        $this->assertInstanceOf(Route::class, $route_e);
        $this->assertSame('Output E', (string) $route_e->run()->getBody());

        // Test with parameters.
        $closure_f = fn (string $a): string => sprintf('Output %s', $a);
        $this->router->setCallableRoute('GET', 'routes/:int', $closure_f);
        $route_f = $this->findRoute('GET', 'routes/123');
        $this->assertInstanceOf(Route::class, $route_f);
        $this->assertSame('Output 123', (string) $route_f->run()->getBody());

        // Test with request and server info argument.
        $closure_g = function (
            string $a,
            RequestInterface $b,
            string $c,
            null|ServerInfo $d,
        ): string {
            $host = $b->getUri()->host;
            $prot = $d ? $d->get('SERVER_PROTOCOL', '') : '???';
            return "{$a}, {$host}, {$c}, {$prot}";
        };
        $this->router->setCallableRoute('POST', 'routes/:int/test/:alpha', $closure_g);
        $route_g1 = $this->findRoute('POST', 'routes/123/test/abc');
        $this->assertInstanceOf(Route::class, $route_g1);
        $this->assertSame(
            '123, foobar.com, abc, HTTP/1.1',
            (string) $route_g1->run()->getBody(),
        );
        $route_g2 = $this->findRoute('POST', 'routes/123/test/abc', false);
        $this->assertInstanceOf(Route::class, $route_g2);
        $this->assertSame(
            '123, foobar.com, abc, ???',
            (string) $route_g2->run()->getBody(),
        );

        // Test with variadic string argument.
        $closure_h = function (string $a, string ...$b): string {
            return $a . ', ' . implode(', ', $b);
        };
        $path_h = 'foos/:int/bars/:int/bazes/:int';
        $this->router->setCallableRoute('POST', $path_h, $closure_h);
        $route_h = $this->findRoute('POST', 'foos/1/bars/9/bazes/0');
        $this->assertInstanceOf(Route::class, $route_h);
        $this->assertSame('1, 9, 0', (string) $route_h->run()->getBody());

        // Test pushing prefix.
        $this->assertSame($this->router, $this->router->pushPrefix('prefix'));
        $this->router->setCallableRoute('GET', 'path/a', fn (): string => 'Path A');
        $route_i = $this->findRoute('GET', 'prefix/path/a');
        $this->assertInstanceOf(Route::class, $route_i);
        $content_i = (string) $route_i->run()->getBody();
        $this->assertSame('Path A', $content_i);

        // Test popping prefix.
        $this->assertSame($this->router, $this->router->popPrefix());
        $this->router->setCallableRoute('GET', 'path/b', fn (): string => 'Path B');
        $route_j = $this->findRoute('GET', 'path/b');
        $this->assertInstanceOf(Route::class, $route_j);
        $content_j = (string) $route_j->run()->getBody();
        $this->assertSame('Path B', $content_j);

        // Test prefix trimming.
        $this->router
            ->pushPrefix('/animals')
                ->setCallableRoute('GET', 'dog', fn (): string => 'Dog!')
            ->popPrefix()
            ->pushPrefix('plants/')
                ->setCallableRoute('GET', 'tree', fn (): string => 'Tree!')
            ->popPrefix()
            ->pushPrefix('/plants/')
                ->pushPrefix('flowers')
                    ->setCallableRoute('GET', 'poppy', fn (): string => 'Poppy!')
                ->popPrefix()
            ->popPrefix();
        $tests = [
            ['animals/dog', 'Dog!'],
            ['plants/tree', 'Tree!'],
            ['plants/flowers/poppy', 'Poppy!'],
        ];
        foreach ($tests as $test) {
            $route = $this->findRoute('GET', $test[0]);
            $this->assertInstanceOf(Route::class, $route);
            $content = (string) $route->run()->getBody();
            $this->assertSame($test[1], $content);
        }

        // Test with object methods.
        $this->router
            ->setClassRoute('GET', 'greeting', Example::class, 'greet', 'Carl')
            ->setClassRoute('GET', 'farewell', Example::class, 'sayBye');
        $tests = [
            ['greeting', 'Hello, Carl!'],
            ['farewell', 'Goodbye, John!'],
        ];
        foreach ($tests as $test) {
            $route = $this->findRoute('GET', $test[0]);
            $this->assertInstanceOf(Route::class, $route);
            $content = (string) $route->run()->getBody();
            $this->assertSame($test[1], $content);
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
            ->setPreludes('p1', 'p3')
                ->setCallableRoute('GET', 'route-a', $closure)
            ->setPreludes()
                ->setCallableRoute('GET', 'route-b', $closure)
            ->setPreludes('p2')
                ->setCallableRoute('GET', 'route-c', $closure)
            ->setPreludes('p3')
                ->setCallableRoute('GET', 'route-d', $closure);
        
        // Test routes.
        $tests = [
            ['route-a', 'Hello, World!'],
            ['route-b', 'Hello, World!'],
            ['route-c', 'Hello, Universe!'],
            ['route-d', 'Hello, World!'],
        ];
        foreach ($tests as [$path, $expected]) {
            $route = $this->findRoute('GET', $path);
            $this->assertInstanceOf(Route::class, $route);
            $content = (string) $route->run()->getBody();
            $this->assertSame($expected, $content);
        }
    }

    /**
     * Find a route using a generic request with the given method and path.
     */
    protected function findRoute(
        string $method,
        string $path,
        bool $create_server_info = true,
    ): null|Route {
        $request = new IncomingRequest(
            content_or_post: 'Hello, World!',
            headers: [],
            protocol_version: null,
            method: $method,
            uri: 'http://foobar.com/' . $path,
            parameters: [],
            cookies: [],
        );

        $server = $create_server_info
            ? new ServerInfo(['SERVER_PROTOCOL' => 'HTTP/1.1'])
            : null;

        return $this->router->findRoute($request, $server);
    }

    protected function setUp(): void
    {
        $this->router = new Router();
    }
}

class Example
{
    public function __construct(protected string $name = 'John')
    {
    }

    public function greet(): string
    {
        return "Hello, {$this->name}!";
    }

    public function sayBye(): string
    {
        return "Goodbye, {$this->name}!";
    }
}

class PreludeA extends AbstractRoutePrelude
{
    public static int $run_count = 0;

    public function run(): null|string
    {
        static::$run_count++;

        if ($this->parameters[0] ?? null) {
            return $this->parameters[0];
        }

        return null;
    }
}

class PreludeB extends AbstractRoutePrelude
{
    public static int $run_count = 0;

    public function run(): null|ResponseInterface
    {
        static::$run_count++;
        return null;
    }
}