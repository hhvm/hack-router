<?hh // strict
/*
 *  Copyright (c) 2015-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the BSD-style license found in the
 *  LICENSE file in the root directory of this source tree. An additional grant
 *  of patent rights can be found in the PATENTS file in the same directory.
 *
 */

namespace Facebook\HackRouter;

class UnknownRouterException extends InternalServerErrorException {
  public function __construct(private array<mixed> $fastRouteData) {
    parent::__construct(
      'Unknown FastRoute response: '.var_export($fastRouteData, true),
    );
  }

  public function getFastRouteData(): array<mixed> {
    return $this->fastRouteData;
  }
}
