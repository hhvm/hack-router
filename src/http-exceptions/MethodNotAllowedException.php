<?hh // strict
/*
 *  Copyright (c) 2015-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace Facebook\HackRouter;

class MethodNotAllowedException extends HttpException {
  public function __construct(
    protected keyset<HttpMethod> $allowed,
    string $message = '',
    int $code = 0,
    ?\Exception $previous = null,
  ) {
    parent::__construct($message, $code, $previous);
  }

  public function getAllowedMethods(): keyset<HttpMethod> {
    return $this->allowed;
  }
}
