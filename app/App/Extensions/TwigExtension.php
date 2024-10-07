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

namespace App\Extensions;

use App\Helpers\RequestHelper;
use Psr\Container\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use function env;

class TwigExtension extends AbstractExtension
{
    protected mixed $_config;
    protected mixed $_csrf;
    protected ContainerInterface $_container;

    public function __construct(ContainerInterface $container) {
        $this->_config = $container->get("config");
        $this->_csrf = $container->get("csrf");
        $this->_container = $container;
    }

    public function getFunctions() {
        return [
            new TwigFunction("getenv", [$this, "getenv"]),
            new TwigFunction("config", [$this, "config"]),
            new TwigFunction("csrf", [$this, "csrf"]),
            new TwigFunction("route_requires_captcha", [$this, "routeRequiresCAPTCHA"]),
        ];
    }

    public function getenv($key, $default = null) {
        return env($key, $default);
    }

    public function config($key) {
        return $this->_config->get($key);
    }

    public function csrf(): string {
        return '
            <input type="hidden" name="' . $this->_csrf->getTokenNameKey() . '" value="' . $this->_csrf->getTokenName() . '">
            <input type="hidden" name="' . $this->_csrf->getTokenValueKey() . '" value="' . $this->_csrf->getTokenValue() . '">
        ';
    }

    public function routeRequiresCAPTCHA($name): bool {
        return RequestHelper::routeRequiresCAPTCHA($this->_container, $name);
    }
}