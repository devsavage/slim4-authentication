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

class Session
{
    public static function get($key, $default = null) {
        if(self::exists($key)) {
            return $_SESSION[$key];
        }

        return $default;
    }

    public static function exists($key): bool {
        return isset($_SESSION[$key]);
    }

    public static function destroy($key): void {
        if(self::exists($key)) {
            unset($_SESSION[$key]);
        }
    }

    public static function set($name, $value): void {
        $_SESSION[$name] = $value;
    }

    public static function put($name, $value): void {
        self::set($name, $value);
    }

    public static function create($name, $value): void {
        self::set($name, $value);
    }
}