<?php
/*
 * Copyright (c) 2024 devsavage (https://github.com/devsavage)
 * MIT License
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace App\Http\Middleware;

use App\Helpers\Cookie;
use App\Helpers\Hash;
use App\Helpers\Session;
use App\Http\Middleware\Middleware;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function count;
use function explode;
use function trim;

class RememberMiddleware extends Middleware
{
    protected $_key;

    public function __construct(ContainerInterface $container) {
        parent::__construct($container);

        $this->_key = $this->config("app.remember_id");
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        if(Cookie::exists($this->_key)) {
            $auth = $this->getContainer()->get("auth");

            if(!$auth->check()) {
                $authCookie = Cookie::get($this->_key);
                $credentials = explode(".", $authCookie);

                if(empty(trim($authCookie)) || count($credentials) !== 2) {
                    Cookie::destroy($this->_key);

                    return $handler->handle($request);
                }

                $id = $credentials[0];
                $token = Hash::encryptString($credentials[1]);

                $user = $auth->where("remember_identifier", $id)->first();

                if($user) {
                    if(Hash::verify($token, $user->remember_token)) {
                        Session::set($this->config("app.auth_id"), $user->id);

                        return $handler->handle($request)->withHeader("Location", full_uri($request->getUri()->getPath()));
                    } else {
                        Cookie::destroy($this->_key);

                        $user->removeRememberCredentials();
                    }
                }
            }
        }

        return $handler->handle($request);
    }
}