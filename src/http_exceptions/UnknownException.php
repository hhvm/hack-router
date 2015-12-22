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

final class UnknownException extends RoutingException {
  public function __construct(
    private array<mixed> $fastRouteResult,
    string $method,
    string $path,
  ) {
    parent::__construct(
      "Unknown FastRoute result: ".var_export($fastRouteResult, true),
      $method,
      $path,
    );
  }

  public function getFastRouteResult(): array<mixed> {
    return $this->fastRouteResult;
  }
}
