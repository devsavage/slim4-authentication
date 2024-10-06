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

namespace App\Http\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Interfaces\RouteParserInterface;

class Controller
{
    protected ContainerInterface $_container;
    protected $_view;
    protected $_router;

    public function __construct(ContainerInterface $container) {
        $this->_container = $container;
        $this->_view = $container->get("view");
        $this->_router = $container->get(RouteParserInterface::class);
    }

    public function __get($property) {
        if ($this->_container->has($property)) {
            return $this->_container->get($property);
        }

        return null;
    }

    protected function render(Response $response, string $template, array $params = []): Response {
        return $this->_view->render($response, $template . ".twig", $params);
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
}