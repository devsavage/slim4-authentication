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

namespace App\Validation;

use App\Helpers\RequestHelper;
use App\Helpers\Session;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Exceptions\NestedValidationException;
use function ucfirst;

class Validator
{
    protected array $errors = [];

    public function validate(Request $request, array $rules = []): static {
        foreach ($rules as $field => $rule) {
            try {
                $rule->setName(ucfirst($field))->assert(RequestHelper::param($request, $field));
            } catch (NestedValidationException $validationException) {
                foreach ($validationException->getIterator() as $message) {
                    $this->errors[$field] = $message->getMessage();
                }
            }
        }

        Session::set("errors", $this->errors);

        return $this;
    }

    public function failed(): bool {
        return !empty($this->errors);
    }

    public function errors(): array {
        return $this->errors;
    }
}