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
use App\Extensions\TwigExtension;
use App\Validation\Validator;
use DI\ContainerBuilder;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use Illuminate\Database\Capsule\Manager;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Csrf\Guard;
use Slim\Flash\Messages;
use Slim\Interfaces\RouteParserInterface;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;
use Turnstile\Client\Client;
use Turnstile\Turnstile;
use Twig\Extension\DebugExtension;

return function (ContainerBuilder $builder)
{
    $builder->addDefinitions([
        RouteParserInterface::class => function(ContainerInterface $container) {
            return $container->get(App::class)->getRouteCollector()->getRouteParser();
        },

        "twig" => function(ContainerInterface $container) {
            $config = $container->get("config");

            return Twig::create($config->get("app.view.template_path"), $config->get("app.view.twig"));
        },

        "auth" => function(ContainerInterface $container) {
            return new Auth();
        },

        "view" => function(ContainerInterface $container) {
            $twig = $container->get("twig");

            $twig->addExtension(new DebugExtension());
            $twig->addExtension(new TwigExtension($container));

            $twig->getEnvironment()->addGlobal("flash", $container->get("flash"));

            $twig->getEnvironment()->addGlobal("auth", [
                "check" => $container->get("auth")->check(),
                "user" => $container->get("auth")->user(),
            ]);

            $routeParser = $container->get(RouteParserInterface::class);

//            $twig->getEnvironment()->addGlobal("route", $twig->get);

            return $twig;
        },

        "database" => function(ContainerInterface $container) {
            $config = $container->get("config");

            $capsule = new Manager();
            $capsule->addConnection($config->get("database"));

            $capsule->setAsGlobal();

            return $capsule;
        },

        "guzzle" => function(ContainerInterface $container) {
            return new GuzzleHttpClient();
        },

        "turnstile_client" => function(ContainerInterface $container) {
            return new Client($container->get("guzzle"), new HttpFactory());
        },

        "turnstile" => function(ContainerInterface $container) {
            $config = $container->get("config");

            return new Turnstile(client: $container->get("turnstile_client"), secretKey: $config->get("plugins.turnstile.secret"));
        },

        "validator" => function(ContainerInterface $container) {
            return new Validator();
        },

        "flash" => function(ContainerInterface $container) {
            return new Messages();
        },

        "csrf" => function(ContainerInterface $container) {
            $config = $container->get("config");
            $flash = $container->get("flash");
            $responseFactory = $container->get(App::class)->getResponseFactory();
            $guard = new Guard($responseFactory);

            $guard->setFailureHandler(function (ServerRequestInterface $request) use ($responseFactory, $container, $config, $flash) {
                $flash->addMessage("error", $config->get("lang.csrf_failed"));

                $response = $responseFactory->createResponse();
                $routeContext = RouteContext::fromRequest($request);
                $route = $routeContext->getRoute();

                return $response->withHeader("Location", full_uri($routeContext->getRouteParser()->urlFor($route->getName())));
            });

            return $guard;
        },
    ]);
};