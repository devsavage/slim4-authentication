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

namespace App\Helpers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use function array_key_exists;
use function array_keys;
use function array_values;
use function dd;
use function in_array;
use function is_array;

class RequestHelper
{
    public static function param(ServerRequestInterface $request, $name, $queryParamsOnly = false)
    {
        if($request->getMethod() == "GET" && $request->getQueryParams() || $queryParamsOnly) {
            return array_key_exists($name, $request->getQueryParams()) ? $request->getQueryParams()[$name] : null;
        }

        if($request->getParsedBody()) {
            return array_key_exists($name, $request->getParsedBody()) ? $request->getParsedBody()[$name] : null;
        }

        return null;
    }

    public static function routeRequiresCAPTCHA(ContainerInterface $container, $route): bool {
        $config = $container->get("config");
        $enabled = $config->get("plugins.turnstile.enabled");

        if(!$enabled) {
            return false;
        }

        $validRoutes = $config->get("plugins.turnstile.enabled_routes");

        if(!is_array($validRoutes) || count($validRoutes) == 0) {
            return false;
        }

        return in_array($route, $validRoutes);
    }
}