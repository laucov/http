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

namespace Tests\Routing\Call;

use Laucov\Http\Message\IncomingResponse;
use Laucov\Http\Message\OutgoingResponse;
use Laucov\Http\Message\ResponseInterface;
use Laucov\Http\Routing\Call\Callback;
use Laucov\Http\Routing\Call\Interfaces\PreludeInterface;
use Laucov\Http\Routing\Call\Route;
use Laucov\Http\Routing\Exceptions\HttpException;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Http\Routing\Call\Route
 * @todo Test PreludeInterface requirement.
 */
class RouteTest extends TestCase
{
    public function callbackProvider(): array
    {
        // Create callbacks.
        $callbacks = [
            new Callback(
                fn ($name) => "Hello, {$name}!",
                null,
                [],
            ),
            new Callback(
                fn ($x, $y) => new IncomingResponse('x + y = ' . ($x + $y)),
                null,
                [],
            ),
            new Callback([B::class, 'f'], [2, 1], []),
            new Callback([B::class, 'f'], [3, 5], []),
            new Callback([B::class, 'g'], [1, 2], []),
        ];

        // Create preludes.
        $preludes = [
            new class () implements PreludeInterface {
                public function run(): null
                {
                    return null;
                }
            },
            new class () implements PreludeInterface {
                public function run(): string
                {
                    return 'Interrupted!';
                }
            },
            new class () implements PreludeInterface {
                public function run(): ResponseInterface
                {
                    return new IncomingResponse('Interrupted again!');
                }
            },
            new class () implements PreludeInterface {
                public function run(): null
                {
                    $response = new OutgoingResponse();
                    $response
                        ->setStatus(400, 'Bad Request')
                        ->setBody('Exception!');
                    throw new HttpException($response);
                }
            },
        ];

        return [
            [[$callbacks[0], ['John'], []], 'Hello, John!', true],
            [[$callbacks[1], [5, 8], []], 'x + y = 13', false],
            [[$callbacks[2], [5], []], '11', true],
            [[$callbacks[2], [3], []], '7', true],
            [[$callbacks[3], [5], []], '20', true],
            [[$callbacks[0], ['Mary'], [$preludes[0]]], 'Hello, Mary!', true],
            [[$callbacks[0], ['Mary'], [$preludes[1]]], 'Interrupted!', true],
            [[$callbacks[0], ['Mary'], [$preludes[2]]], 'Interrupted again!', false],
            [[$callbacks[4], ['1'], []], '3', true],
            [[$callbacks[4], ['0'], []], 'Cannot divide by zero!', false],
            [[$callbacks[4], ['0'], [$preludes[3]]], 'Exception!', false],
        ];
    }

    /**
     * @covers ::__construct
     * @covers ::createResponse
     * @covers ::run
     * @covers ::runCallback
     * @covers ::runPreludes
     * @uses Laucov\Http\Message\AbstractIncomingMessage::__construct
     * @uses Laucov\Http\Message\AbstractMessage::getBody
     * @uses Laucov\Http\Message\AbstractMessage::getHeaderLine
     * @uses Laucov\Http\Message\AbstractOutgoingMessage::setBody
     * @uses Laucov\Http\Message\AbstractOutgoingMessage::setHeaderLine
     * @uses Laucov\Http\Message\IncomingResponse::__construct
     * @uses Laucov\Http\Message\OutgoingResponse::setStatus
     * @uses Laucov\Http\Message\Traits\ResponseTrait::getStatusCode
     * @uses Laucov\Http\Message\Traits\ResponseTrait::getStatusText
     * @uses Laucov\Http\Routing\Exceptions\HttpException::__construct
     * @uses Laucov\Http\Routing\Exceptions\HttpException::getResponse
     * @dataProvider callbackProvider
     */
    public function testCanSetupAndRun(
        array $args,
        string $expected,
        bool $assert_default_headers,
    ): void {
        // Create route and get response.
        $route = new Route(...$args);
        $response = $route->run();
        $this->assertIsObject($response);

        // Check headers.
        if ($assert_default_headers) {
            $content_length = $response->getHeaderLine('Content-Length');
            $this->assertSame((string) strlen($expected), $content_length);
            $content_type = $response->getHeaderLine('Content-Type');
            $this->assertSame('text/html', $content_type);
        }

        // Check content.
        $this->assertSame($expected, (string) $response->getBody());
    }

    /**
     * @covers ::createResponse
     * @uses Laucov\Http\Routing\Call\Callback::__construct
     * @uses Laucov\Http\Routing\Call\Route::__construct
     * @uses Laucov\Http\Routing\Call\Route::run
     * @uses Laucov\Http\Routing\Call\Route::runCallback
     * @uses Laucov\Http\Routing\Call\Route::runPreludes
     */
    public function testCallbackMustReturnValidOutput(): void
    {
        // The output must be a string, a stringable object or a response.
        $this->expectException(\RuntimeException::class);
        $closure = fn () => ['foo', 'bar'];
        $callback = new Callback($closure, null, []);
        $route = new Route($callback, [], []);
        $route->run();
    }
}

/**
 * Provide some functions for route testing.
 */
class B
{
    /**
     * Create instance.
     */
    public function __construct(
        protected int $a,
        protected int $b,
    ) {
    }

    /**
     * Calculate `f(x) = ax + b`.
     */
    public function f(int $x): string
    {
        // Calculate.
        $y = $this->a * $x + $this->b;

        return (string) $y;
    }

    /**
     * Calculate `f(x) = (ax + b) / x`.
     */
    public function g(int $x): string
    {
        // Check X value.
        if ($x === 0) {
            $response = new OutgoingResponse();
            $response
                ->setStatus(422, 'Unprocessable Entity')
                ->setBody('Cannot divide by zero!');
            throw new HttpException($response);
        }

        // Calculate.
        $y = ($this->a * $x + $this->b) / $x;

        return (string) $y;
    }
}
