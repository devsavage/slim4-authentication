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

namespace App\Http\Controllers\Auth\Account;

use App\Helpers\RequestHelper;
use App\Helpers\Session;
use App\Http\Controllers\Controller;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;

class AccountController extends Controller
{
    public function index(Request $request, Response $response): Response {
        return $this->render($request, $response, "auth/account/index");
    }

    public function update(Request $request, Response $response): Response {
        $email = RequestHelper::param($request, "email");

        $validation = $this->validator->validate($request, [
            "email" => v::notEmpty()->email()->isUniqueEmail($this->auth)->length(null, 75),
        ]);

        if($validation->failed()) {
            $this->flash("error", $this->config("lang.update_profile_validation_failed"));
            $this->flash("errors", Session::get("errors"));
            return $this->redirect($response, "auth.account");
        }

        if($email !== $this->auth->user()->email) {
            $this->auth->user()->update([
                "email" => $email,
            ]);
        }

        $this->flash("success", $this->config("lang.update_profile_success"));
        return $this->redirect($response, "auth.account");
    }
}