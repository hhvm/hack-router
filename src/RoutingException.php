<?hh // strict
/*
 *  Copyright (c) 2015, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the BSD-style license found in the
 *  LICENSE file in the root directory of this source tree. An additional grant
 *  of patent rights can be found in the PATENTS file in the same directory.
 *
 */

namespace Facebook\HackRouter;

abstract class RoutingException extends \Exception {
  protected function __construct(
    string $message,
    private string $requestMethod,
    private string $requestedPath,
  ) {
    parent::__construct($message);
  }

  public function getRequestMethod(): string {
    return $this->requestMethod;
  }

  public function getRequestedPath(): string {
    return $this->requestedPath;
  }
}
