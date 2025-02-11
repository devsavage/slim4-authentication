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

use App\Helpers\RequestHelper;
use App\Helpers\Session;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Interfaces\RouteParserInterface;
use Slim\Routing\RouteContext;
use function array_merge;
use function full_uri;

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

    public function config($key) {
        return $this->config->get($key);
    }

    protected function flash($type, $message, $forNow = false) {
        if($forNow) {
            return $this->flash->addMessageNow($type, $message);
        }

        return $this->flash->addMessage($type, $message);
    }

    protected function render(Request $request, Response $response, string $template, array $params = []): Response {
        $twigParams = array_merge([
            "routeName" => RouteContext::fromRequest($request)->getRoute()->getName(),
        ], $params);

        Session::destroy("old");

        return $this->_view->render($response, $template . ".twig", $twigParams);
    }

    protected function redirect(Response $response, $route, $urlArgs = [], $urlParams = [], $additionalQuery = null): Response {
        if($additionalQuery) {
            return $this->redirectTo($response, $this->buildUrl($this->_router->urlFor($route, $urlArgs, $urlParams) . $additionalQuery));
        }

        return $this->redirectTo($response, $this->buildUrl($this->_router->urlFor($route, $urlArgs, $urlParams)));
    }

    public function requiresCAPTCHA($request): bool {
        return RequestHelper::routeRequiresCAPTCHA($this->_container, RouteContext::fromRequest($request)->getRoute()->getName());
    }

    protected function redirectTo(Response $response, $to): Response {
        return $response->withHeader("Location", $to);
    }

    private function buildUrl($url): string {
        return full_uri($url);
    }
}