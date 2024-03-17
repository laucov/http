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
use Laucov\Http\Routing\Route;
use Laucov\Http\Routing\Router;
use Laucov\Http\Server\ServerInfo;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Http\Routing\Router
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
     * @covers ::setClassRoute
     * @covers ::setClosureRoute
     * @covers ::setPattern
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
     * @uses Laucov\Http\Routing\Route::__construct
     * @uses Laucov\Http\Routing\Route::run
     * @uses Laucov\Http\Routing\Route::createResponse
     * @uses Laucov\Http\Routing\RouteClassMethod::__construct
     * @uses Laucov\Http\Routing\RouteClosure::__construct
     * @uses Laucov\Http\Routing\Router::__construct
     * @uses Laucov\Http\Server\ServerInfo::__construct
     * @uses Laucov\Http\Server\ServerInfo::get
     */
    public function testCanSetAndFindRoutes(): void
    {
        // Set route.
        $closure_a = fn (): string => 'Output A';
        $this->assertSame(
            $this->router,
            $this->router->setClosureRoute('GET', 'path/to/route-a', $closure_a),
        );

        // Get existent route.
        $route_a = $this->findRoute('GET', 'path/to/route-a');
        $this->assertInstanceOf(Route::class, $route_a);
        $this->assertSame('Output A', (string) $route_a->run()->getBody());

        // Get inexistent route with inexistent segment.
        $this->assertNull($this->findRoute('GET', 'path/to/route-b'));
        // Get inexistent route with intermediary segment.
        $this->assertNull($this->findRoute('GET', 'path/to'));
        // Get inexistent route with wrong method.
        $this->assertNull($this->findRoute('POST', 'path/to/route-a'));

        // Test router's path trimming.
        $closure_b = fn (): string => 'Output B';
        $this->router->setClosureRoute('POST', 'path/to/route-b/', $closure_b);
        $route_b = $this->findRoute('POST', 'path/to/route-b');
        $this->assertInstanceOf(Route::class, $route_b);
        $this->assertSame('Output B', (string) $route_b->run()->getBody());
        $closure_c = fn (): string => 'Output C';
        $this->router->setClosureRoute('PUT', '/path/to/route-c', $closure_c);
        $route_c = $this->findRoute('PUT', 'path/to/route-c');
        $this->assertInstanceOf(Route::class, $route_c);
        $this->assertSame('Output C', (string) $route_c->run()->getBody());
        $closure_d = fn (): string => 'Output D';
        $this->router->setClosureRoute('PATCH', '/path/to/route-d/', $closure_d);
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
        $this->router->setClosureRoute('POST', 'routes/:alpha', $closure_e);
        $route_e = $this->findRoute('POST', 'routes/e');
        $this->assertInstanceOf(Route::class, $route_e);
        $this->assertSame('Output E', (string) $route_e->run()->getBody());

        // Test with parameters.
        $closure_f = fn (string $a): string => sprintf('Output %s', $a);
        $this->router->setClosureRoute('GET', 'routes/:int', $closure_f);
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
        $this->router->setClosureRoute('POST', 'routes/:int/test/:alpha', $closure_g);
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
        $this->router->setClosureRoute('POST', $path_h, $closure_h);
        $route_h = $this->findRoute('POST', 'foos/1/bars/9/bazes/0');
        $this->assertInstanceOf(Route::class, $route_h);
        $this->assertSame('1, 9, 0', (string) $route_h->run()->getBody());

        // Test pushing prefix.
        $this->assertSame($this->router, $this->router->pushPrefix('prefix'));
        $this->router->setClosureRoute('GET', 'path/a', fn (): string => 'Path A');
        $route_i = $this->findRoute('GET', 'prefix/path/a');
        $this->assertInstanceOf(Route::class, $route_i);
        $content_i = (string) $route_i->run()->getBody();
        $this->assertSame('Path A', $content_i);

        // Test popping prefix.
        $this->assertSame($this->router, $this->router->popPrefix());
        $this->router->setClosureRoute('GET', 'path/b', fn (): string => 'Path B');
        $route_j = $this->findRoute('GET', 'path/b');
        $this->assertInstanceOf(Route::class, $route_j);
        $content_j = (string) $route_j->run()->getBody();
        $this->assertSame('Path B', $content_j);

        // Test prefix trimming.
        $this->router
            ->pushPrefix('/animals')
                ->setClosureRoute('GET', 'dog', fn (): string => 'Dog!')
            ->popPrefix()
            ->pushPrefix('plants/')
                ->setClosureRoute('GET', 'tree', fn (): string => 'Tree!')
            ->popPrefix()
            ->pushPrefix('/plants/')
                ->pushPrefix('flowers')
                    ->setClosureRoute('GET', 'poppy', fn (): string => 'Poppy!')
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
     * @uses Laucov\Http\Routing\Route::__construct
     * @uses Laucov\Http\Routing\Route::createResponse
     * @uses Laucov\Http\Routing\Route::run
     * @uses Laucov\Http\Routing\RouteClosure::__construct
     * @uses Laucov\Http\Routing\Router::__construct
     * @uses Laucov\Http\Routing\Router::getRouteKeys
     * @uses Laucov\Http\Routing\Router::setClosureRoute
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
                ->setClosureRoute('GET', 'route-a', $closure)
            ->setPreludes()
                ->setClosureRoute('GET', 'route-b', $closure)
            ->setPreludes('p2')
                ->setClosureRoute('GET', 'route-c', $closure)
            ->setPreludes('p3')
                ->setClosureRoute('GET', 'route-d', $closure);
        
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

        // @todo Assert static::$number
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