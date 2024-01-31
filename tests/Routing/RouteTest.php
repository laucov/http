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

use Laucov\Http\Message\OutgoingResponse;
use Laucov\Http\Message\ResponseInterface;
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
     * @covers ::run
     * @uses Laucov\Files\Resource\StringSource::__construct
     * @uses Laucov\Files\Resource\StringSource::__toString
     * @uses Laucov\Http\Message\AbstractMessage::getBody
     * @uses Laucov\Http\Message\AbstractOutgoingMessage::setBody
     * @uses Laucov\Http\Routing\RouteClosure::__construct
     * @uses Laucov\Http\Routing\RouteClosure::findClosureParameterTypes
     * @uses Laucov\Http\Routing\RouteClosure::findClosureReturnType
     */
    public function testCanRun(): void
    {
        // Create closure with string return type.
        $closure_a = new RouteClosure(fn (string $a): string => $a);
        $route_a = new Route($closure_a, ['Hello, World!']);

        // Create closure with Stringable return type.
        $closure_b = new RouteClosure(function (string $b): \Stringable {
            return new class ($b) implements \Stringable
            {
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
}
