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

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Interfaces\RouteParserInterface;

abstract class Middleware implements MiddlewareInterface
{
    protected ContainerInterface $_container;
    protected RouteParserInterface $_router;

    public function __construct(ContainerInterface $container) {
        $this->_container = $container;
        $this->_router = $container->get(RouteParserInterface::class);
    }

    public function __get($property) {
        if ($this->getContainer()->has($property)) {
            return $this->getContainer()->get($property);
        }

        return null;
    }

    protected function flash($type, $message, $forNow = false) {
        if($forNow) {
            return $this->flash->addMessageNow($type, $message);
        }

        return $this->flash->addMessage($type, $message);
    }

    protected function redirect(Response $response, $route, $urlArgs = [], $urlParams = [], $additionalQuery = null): Response {
        if($additionalQuery) {
            return $this->redirectTo($response, $this->buildUrl($this->_router->urlFor($route, $urlArgs, $urlParams) . $additionalQuery));
        }

        return $this->redirectTo($response, $this->buildUrl($this->_router->urlFor($route, $urlArgs, $urlParams)));
    }

    protected function redirectTo(Response $response, $to): Response {
        return $response->withHeader("Location", $to);
    }

    private function buildUrl($url): string {
        return $this->config("app.url") . $url;
    }

    public function getContainer(): ContainerInterface {
        return $this->_container;
    }

    public function config($key) {
        return $this->config->get($key);
    }
}