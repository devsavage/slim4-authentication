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

class Cookie
{
    public static function exists($name): bool {
        return isset($_COOKIE[$name]);
    }

    public static function get($key, $default = null) {
        if(self::exists($key)) {
            return $_COOKIE[$key];
        }

        return $default;
    }

    public static function set($name, $value, $expiry, $secure = false, $httpOnly = true, $domain = null, $samesite = "Lax"): bool {
        if(setcookie($name, $value, [
            "expires" => $expiry,
            "path" => "/",
            "domain" => $domain,
            "secure" => $secure,
            "httponly" => $httpOnly,
            "samesite" => $samesite
        ])) {
            return true;
        }

        return false;
    }

    public static function destroy($name, $subdomain = false): void {
        if(!self::exists($name)) return;

        if($subdomain) {
            self::set($name, '', time() - 1, false, true, $subdomain);
            return;
        }

        self::set($name, '', time() - 1);
    }
}