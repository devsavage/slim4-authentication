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

use App\Http\Middleware\OldInputMiddleware;
use App\Http\Middleware\RememberMiddleware;
use DI\Bridge\Slim\Bridge;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Noodlehaus\Config;
use Respect\Validation\Factory;
use Slim\Views\TwigMiddleware;

session_start();

require __DIR__ . "/../vendor/autoload.php";

const INC_ROOT = __DIR__;

if($envExists = file_exists(INC_ROOT . "/../.env")) {
    $dotenv = Dotenv::createImmutable(INC_ROOT . "/../");
    $dotenv->load();
}

Factory::setDefaultInstance((new Factory())
    ->withRuleNamespace("App\\Validation\\Rules")
    ->withExceptionNamespace("App\\Validation\\Exceptions")
);

$builder = new ContainerBuilder();

$default = require INC_ROOT . "/../bootstrap/container.php";
$default($builder);

$container = $builder->build();

$container->set("config", function() {
    return new Config(INC_ROOT . "/../config");
});

$config = $container->get("config");

$slim = Bridge::create($container);
$slim->setBasePath($config->get("app.base_path"));

$slim->addMiddleware($container->get("csrf"));
$slim->addMiddleware(new RememberMiddleware($container));

$slim->addBodyParsingMiddleware();
$slim->addRoutingMiddleware();

if($envExists) {
    $container->get("database")->bootEloquent();
    $slim->addMiddleware(TwigMiddleware::createFromContainer($slim));
    $slim->addMiddleware(new OldInputMiddleware($container));
} else {
    dd("Failed to initialize database connection, check your .env file!");
}

$webRoutes = require INC_ROOT . "/../routes/web.php";
$webRoutes($slim, $container);
