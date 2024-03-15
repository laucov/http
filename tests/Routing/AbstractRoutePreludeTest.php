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
use Laucov\Http\Routing\AbstractRoutePrelude;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Http\Routing\AbstractRoutePrelude
 */
class AbstractRoutePreludeTest extends TestCase
{
    /**
     * @covers ::__construct
     * @uses Laucov\Http\Message\AbstractIncomingMessage::__construct
     * @uses Laucov\Http\Message\AbstractMessage::getBody
     * @uses Laucov\Http\Message\IncomingRequest::__construct
     * @uses Laucov\Http\Routing\AbstractRoutePrelude::__construct
     */
    public function testCanRun(): void
    {
        $request = new IncomingRequest('Hello, %s!');
        $prelude = new class ($request, ['John']) extends AbstractRoutePrelude
        {
            public function run(): string
            {
                $body = (string) $this->request->getBody();
                return sprintf($body, ...$this->parameters);
            }
        };

        $this->assertSame('Hello, John!', $prelude->run());
    }
}
