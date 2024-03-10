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

namespace Laucov\Http\Message;

use Laucov\Http\Cookie\ResponseCookie;
use Laucov\Http\Message\Traits\ResponseTrait;

/**
 * Stores information about an incoming response.
 */
class IncomingResponse extends AbstractIncomingMessage implements
    ResponseInterface
{
    use ResponseTrait;

    /**
     * Create the incoming response instance.
     */
    public function __construct(
        mixed $content,
        array $headers,
        null|string $protocol_version,
        int $status_code,
        string $status_text,
        array $cookies,
    ) {
        $this->statusCode = $status_code;
        $this->statusText = $status_text;
        parent::__construct($content, $headers, $protocol_version);
        foreach ($cookies as $cookie) {
            if (!($cookie instanceof ResponseCookie)) {
                $msg = sprintf(
                    'All cookies must be %s objects.',
                    ResponseCookie::class,
                );
                throw new \InvalidArgumentException($msg);
            }
            $this->cookies[$cookie->name] = $cookie;
        }
    }
}
