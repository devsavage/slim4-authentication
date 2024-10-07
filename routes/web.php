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

use App\Auth\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\GuestMiddleware;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use Slim\Interfaces\RouteParserInterface;

return function (App $app, ContainerInterface $container) {
    $app->get("[/]", [HomeController::class, "index"])->setName("home");

    $app->group("/auth", function (Group $group) use ($app, $container) {
        $group->get("/login[/]", [LoginController::class, "index"])
            ->addMiddleware(new GuestMiddleware($container))
            ->setName("auth.login");
        $group->post("/login[/]", [LoginController::class, "login"])
            ->addMiddleware(new GuestMiddleware($container))
            ->setName("auth.login");

        $group->get("/register[/]", [RegisterController::class, "index"])
            ->addMiddleware(new GuestMiddleware($container))
            ->setName("auth.register");
        $group->post("/register[/]", [RegisterController::class, "register"])
            ->addMiddleware(new GuestMiddleware($container))
            ->setName("auth.register");

        $group->get("/logout[/]", function(Response $response) use ($app, $container) {
            Auth::deauth();

            $container->get("flash")->addMessage("success", $container->get("config")->get("lang.logout_success"));
            return $response->withHeader("Location", $container->get(RouteParserInterface::class)->urlFor("home"));
        })->addMiddleware(new AuthMiddleware($container))->setName("auth.logout");
    });
};
