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

namespace Tests\Routing\Exceptions;

use Laucov\Http\Message\OutgoingResponse;
use Laucov\Http\Routing\Exceptions\HttpException;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Http\Routing\Exceptions\HttpException
 */
class HttpExceptionTest extends TestCase
{
    /**
     * Provides status codes and texts.
     */
    public function statusProvider(): array
    {
        return [
            [400, 'Bad Request'],
            [401, 'Unauthorized'],
            [500, 'Internal Server Error'],
            [503, 'Service Unavailable'],
        ];
    }

    /**
     * @covers ::__construct
     * @covers ::getResponse
     * @uses Laucov\Http\Message\OutgoingResponse::setStatus
     * @uses Laucov\Http\Message\Traits\ResponseTrait::getStatusCode
     * @uses Laucov\Http\Message\Traits\ResponseTrait::getStatusText
     * @dataProvider statusProvider
     */
    public function testStoresAnErrorResponse(
        int $status_code,
        string $status_text,
    ): void {
        // Create response.
        $response = new OutgoingResponse();
        $response->setStatus($status_code, $status_text);

        // Throw exception.
        try {
            throw new HttpException($response);
        } catch (HttpException $e) {
            $this->assertSame($status_code, $e->getCode());
            $this->assertSame($status_text, $e->getMessage());
            $this->assertSame($response, $e->getResponse());
            return;
        }
    }
}
