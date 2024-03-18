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

use Laucov\Http\Routing\Call\Callback;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Http\Routing\Call\Callback
 */
class CallbackTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testCanSetup(): void
    {
        // Create as a callable callback.
        $callable = fn (): string => 'Hello, World!';
        $callback = new Callback($callable, null, ['foo', 'bar']);
        $this->assertSame($callable, $callback->callback);
        $this->assertNull($callback->constructorArgs);
        $this->assertIsArray($callback->preludeNames);
        $this->assertCount(2, $callback->preludeNames);
        $this->assertSame('foo', $callback->preludeNames[0]);
        $this->assertSame('bar', $callback->preludeNames[1]);

        // Create as a class method callback.
        $method = [A::class, 'a'];
        $callback = new Callback($method, ['alpha', 'bravo'], ['baz']);
        $this->assertIsArray($callback->callback);
        $this->assertCount(2, $callback->callback);
        $this->assertSame(A::class, $callback->callback[0]);
        $this->assertSame('a', $callback->callback[1]);
        $this->assertIsArray($callback->constructorArgs);
        $this->assertCount(2, $callback->constructorArgs);
        $this->assertSame('alpha', $callback->constructorArgs[0]);
        $this->assertSame('bravo', $callback->constructorArgs[1]);
        $this->assertIsArray($callback->preludeNames);
        $this->assertCount(1, $callback->preludeNames);
        $this->assertSame('baz', $callback->preludeNames[0]);
    }

    /**
     * @covers ::__construct
     */
    public function testPreludeNamesMustBeStrings(): void
    {
        // Unitialized classes must have constructor arguments.
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(Callback::ERROR_INVALID_PRELUDE_NAMES);
        new Callback([A::class, 'a'], ['foo', 'bar'], ['prelude_1', 123]);
    }

    /**
     * @covers ::__construct
     */
    public function testMustPassConstructorArgumentsForClassCallbacks(): void
    {
        // Instances should pass.
        $instance = new A('arg1', 'arg2');
        new Callback([$instance, 'a'], null, []);

        // Unitialized classes must have constructor arguments.
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(Callback::ERROR_NO_CONSTRUCTOR_ARGS);
        new Callback([A::class, 'a'], null, []);
    }
}

class A
{
    public function __construct(string $a, string $b)
    {
    }

    public function a(): void
    {
    }
}
