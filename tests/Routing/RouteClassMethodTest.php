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
use Laucov\Http\Message\RequestInterface;
use Laucov\Http\Message\ResponseInterface;
use Laucov\Http\Routing\RouteClassMethod;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Http\Routing\RouteClassMethod
 */
class RouteClassMethodTest extends TestCase
{
    /**
     * @covers ::__construct
     * @uses Laucov\Http\Routing\Traits\RouteCallableTrait::validate
     * @uses Laucov\Http\Routing\Traits\RouteCallableTrait::validateParameterTypes
     * @uses Laucov\Http\Routing\Traits\RouteCallableTrait::validateReturnType
     */
    public function testCanUseNonStaticMethods(): void
    {
        // @todo Test static methods

        // Test simple method.
        $test_a = new RouteClassMethod(A::class, 'a');
        $this->assertSame('A!', ($test_a->closure)());

        // Test with constructor arguments.
        $test_b = new RouteClassMethod(B::class, 'greet', 'Hello');
        $this->assertSame('Hello, John!', ($test_b->closure)('John'));

        // Test if validates.
        $mock = $this->getMockBuilder(RouteClassMethod::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['validate'])
            ->getMock();
        $mock
            ->expects($this->once())
            ->method('validate')
            ->withConsecutive([$this->isInstanceOf(\ReflectionMethod::class)]);
        $mock->__construct(A::class, 'a');

        // Test static method.
        $test_c = new RouteClassMethod(B::class, 'b');
        $this->assertSame('B!', ($test_c->closure)());
    }
}

class A
{
    public function a(): string
    {
        return 'A!';
    }
}

class B
{
    public static function b(): string
    {
        return 'B!';
    }

    public function __construct(protected string $greeting)
    {}

    public function greet(string $name): string
    {
        return "{$this->greeting}, {$name}!";
    }
}
