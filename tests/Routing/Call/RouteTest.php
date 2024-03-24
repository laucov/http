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
use Laucov\Http\Message\ResponseInterface;
use Laucov\Http\Routing\Call\Callback;
use Laucov\Http\Routing\Call\Interfaces\PreludeInterface;
use Laucov\Http\Routing\Call\Route;
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
            new Callback([B::class, 'y'], [2, 1], []),
            new Callback([B::class, 'y'], [3, 5], []),
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
        ];

        return [
            [[$callbacks[0], ['John'], []], 'Hello, John!'],
            [[$callbacks[1], [5, 8], []], 'x + y = 13'],
            [[$callbacks[2], [5], []], '11'],
            [[$callbacks[2], [3], []], '7'],
            [[$callbacks[3], [5], []], '20'],
            [[$callbacks[0], ['Mary'], [$preludes[0]]], 'Hello, Mary!'],
            [[$callbacks[0], ['Mary'], [$preludes[1]]], 'Interrupted!'],
            [[$callbacks[0], ['Mary'], [$preludes[2]]], 'Interrupted again!'],
        ];
    }

    /**
     * @covers ::__construct
     * @covers ::createResponse
     * @covers ::run
     * @uses Laucov\Http\Message\AbstractIncomingMessage::__construct
     * @uses Laucov\Http\Message\AbstractMessage::getBody
     * @uses Laucov\Http\Message\AbstractOutgoingMessage::setBody
     * @uses Laucov\Http\Message\IncomingResponse::__construct
     * @dataProvider callbackProvider
     */
    public function testCanSetupAndRun(array $args, string $expected): void
    {
        $route = new Route(...$args);
        $response = $route->run();
        $this->assertIsObject($response);
        $content = (string) $response->getBody();
        $this->assertSame($expected, $content);
    }

    /**
     * @covers ::createResponse
     * @uses Laucov\Http\Routing\Call\Callback::__construct
     * @uses Laucov\Http\Routing\Call\Route::__construct
     * @uses Laucov\Http\Routing\Call\Route::run
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

class B
{
    public function __construct(
        protected int $a,
        protected int $b,
    ) {
    }

    public function y(int $x): string
    {
        return (string) ($this->a * $x + $this->b);
    }
}
