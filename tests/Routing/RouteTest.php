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
use Laucov\Http\Routing\Route;
use Laucov\Http\Routing\RouteClosure;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Http\Routing\Route
 */
class RouteTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::createResponse
     * @covers ::run
     * @uses Laucov\Http\Message\AbstractMessage::getBody
     * @uses Laucov\Http\Message\AbstractOutgoingMessage::setBody
     * @uses Laucov\Http\Routing\AbstractRouteCallable::validate
     * @uses Laucov\Http\Routing\AbstractRouteCallable::validateParameterTypes
     * @uses Laucov\Http\Routing\AbstractRouteCallable::validateReturnType
     * @uses Laucov\Http\Routing\RouteClosure::__construct
     */
    public function testCanRun(): void
    {
        // Create closure with string return type.
        $closure_a = new RouteClosure(fn (string $a): string => $a);
        $route_a = new Route($closure_a, ['Hello, World!']);

        // Create closure with Stringable return type.
        $closure_b = new RouteClosure(function (string $b): \Stringable {
            return new class ($b) implements \Stringable {
                public function __construct(protected string $b)
                {
                }
                public function __toString(): string
                {
                    return $this->b;
                }
            };
        });
        $route_b = new Route($closure_b, ['Hello, World!']);

        // Create closure with ResponseInterface return type.
        $closure_c = new RouteClosure(function (string $c): ResponseInterface {
            $response = new OutgoingResponse();
            return $response->setBody($c);
        });
        $route_c = new Route($closure_c, ['Hello, World!']);

        // Check each output.
        foreach ([$route_a, $route_b, $route_c] as $route) {
            $response = $route->run();
            $this->assertInstanceOf(ResponseInterface::class, $response);
            $this->assertSame('Hello, World!', (string) $response->getBody());
        }
    }

    /**
     * @covers ::run
     * @uses Laucov\Http\Message\AbstractIncomingMessage::__construct
     * @uses Laucov\Http\Message\AbstractMessage::getBody
     * @uses Laucov\Http\Message\AbstractOutgoingMessage::setBody
     * @uses Laucov\Http\Message\IncomingRequest::__construct
     * @uses Laucov\Http\Routing\AbstractRouteCallable::validate
     * @uses Laucov\Http\Routing\AbstractRouteCallable::validateParameterTypes
     * @uses Laucov\Http\Routing\AbstractRouteCallable::validateReturnType
     * @uses Laucov\Http\Routing\Route::__construct
     * @uses Laucov\Http\Routing\Route::createResponse
     * @uses Laucov\Http\Routing\RouteClosure::__construct
     */
    public function testCanUsePreludes(): void
    {
        // Create closure.
        $closure = new RouteClosure(fn (): string => 'Hello, World!');
        $request = new IncomingRequest('');

        // Create preludes.
        $p1 = new Prelude1($request, []);
        $p2 = new Prelude2($request, []);

        // Run without them.
        $route = new Route($closure, [], []);
        $response = $route->run();
        $this->assertSame('Hello, World!', (string) $response->getBody());

        // Set route preludes.
        $route = new Route($closure, [], [$p1, $p2]);
        $response = $route->run();
        $this->assertSame('Hello, World!', (string) $response->getBody());
        $this->assertTrue(Prelude1::$tested);
        $this->assertTrue(Prelude2::$tested);

        // Create interrupting prelude.
        $p3 = new Prelude1($request, [true]);
        Prelude1::$tested = false;
        Prelude2::$tested = false;
        $route = new Route($closure, [], [$p1, $p3, $p2]);
        $response = $route->run();
        $this->assertSame('Hello, Everyone!', (string) $response->getBody());
        $this->assertTrue(Prelude1::$tested);
        $this->assertFalse(Prelude2::$tested);
    }
}

class Prelude1 implements PreludeInterface
{
    public static bool $tested = false;

    public function __construct(
        protected RequestInterface $request,
        protected array $parameters,
    ) {
    }

    public function run(): null|string
    {
        if ($this->parameters[0] ?? false) {
            return 'Hello, Everyone!';
        }

        static::$tested = true;
        return null;
    }
}

class Prelude2 implements PreludeInterface
{
    public static bool $tested = false;

    public function __construct(
        protected RequestInterface $request,
        protected array $parameters,
    ) {
    }

    public function run(): null
    {
        static::$tested = true;
        return null;
    }
}
