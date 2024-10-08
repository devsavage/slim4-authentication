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

namespace App\Http\Controllers\Auth;

use App\Database\User;
use App\Helpers\Cookie;
use App\Helpers\Hash;
use App\Helpers\RequestHelper;
use App\Helpers\Session;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;
use function password_verify;
use function trigger_error;
use const E_CORE_ERROR;
use const E_USER_ERROR;

class LoginController extends Controller
{
    public function index(Request $request, Response $response): Response {
        return $this->render($request, $response, "auth/login");
    }

    public function login(Request $request, Response $response): Response {
        $email = RequestHelper::param($request, "email");
        $password = RequestHelper::param($request, "password");
        $remember = RequestHelper::param($request, "remember");
        $turnstileResponse = RequestHelper::param($request, "cf-turnstile-response");

        if($this->config("plugins.turnstile.enabled") && $this->requiresCAPTCHA($request)) {
            if($turnstileResponse == null) {
                trigger_error("CAPTCHA is enabled on this route but not correctly setup on the view.", E_USER_ERROR);
            }

            if(!$this->turnstile->verify($turnstileResponse, $this->config("plugins.turnstile.cf_secured") ? $request->getServerParams()["HTTP_CF_CONNECTING_IP"] : $request->getServerParams()["REMOTE_ADDR"])->success) {
                $this->flash("error", $this->config("lang.captcha_failed"));
                return $this->redirect($response, "auth.login");
            }
        }

        $validation = $this->validator->validate($request, [
            "email" => v::notEmpty()->email(),
            "password" => v::notEmpty()
        ]);

        if($validation->failed()) {
            $this->flash("error", $this->config("lang.login_validation_failed"));
            $this->flash("errors", Session::get("errors"));
            return $this->redirect($response, "auth.login");
        }

        $user = User::where("email", $email)->first();

        if(!$user || !password_verify($password, $user->password)) {
            $this->flash("error", $this->config("lang.login_failed"));
            $this->flash("errors", Session::get("errors"));
            return $this->redirect($response, "auth.login");
        }

        if($remember === "on") {
            $rememberIdentifier = Hash::random(128);
            $rememberToken = Hash::random(128);

            $hashedToken = Hash::encryptString($rememberToken);

            $user->updateRememberCredentials($rememberIdentifier, $hashedToken);

            Cookie::set($this->config("app.remember_id"), "{$rememberIdentifier}.{$rememberToken}", Carbon::now()->addWeeks(2)->timestamp);
        }

        Session::set($this->config("app.auth_id"), $user->id);

        $this->flash("success", $this->config("lang.login_success"));
        return $this->redirect($response, "home");
    }
}