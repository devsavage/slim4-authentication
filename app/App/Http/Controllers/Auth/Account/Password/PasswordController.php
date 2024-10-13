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

namespace App\Http\Controllers\Auth\Account\Password;

use App\Helpers\RequestHelper;
use App\Helpers\Session;
use App\Http\Controllers\Controller;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;
use function password_hash;
use const PASSWORD_DEFAULT;

class PasswordController extends Controller
{
    public function update(Request $request, Response $response): Response {
        return $this->render($request, $response, "auth/account/password/update");
    }

    public function postUpdate(Request $request, Response $response): Response {
        $newPassword = RequestHelper::param($request, "new_password");

        $validation = $this->validator->validate($request, [
            "password" => v::notEmpty()->matchesCurrentPassword($this->auth),
            "new_password" => v::notEmpty()->length(6),
            "confirm_new_password" => v::notEmpty(),
        ]);

        if($validation->failed()) {
            $this->flash("error", $this->config("lang.update_password_validation_failed"));
            $this->flash("errors", Session::get("errors"));
            return $this->redirect($response, "auth.account.password.update");
        }

        $doesNewPasswordMatch = v::keyValue("confirm_new_password", "equals", "new_password")->validate($request->getParsedBody());

        if(!$doesNewPasswordMatch) {
            $this->flash("error", $this->config("lang.update_password_validation_failed"));

            Session::set("errors", [
                "confirm_password" => "Confirm new password must match new password"
            ]);

            $this->flash("errors", Session::get("errors"));

            return $this->redirect($response, "auth.account.password.update");
        }

        $this->auth->user()->update([
            "password" => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);

        $this->flash("success", $this->config("lang.update_password_success"));
        return $this->redirect($response, "auth.account.password.update");
    }
}