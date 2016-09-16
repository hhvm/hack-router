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

namespace Facebook\HackRouter\Tests;

use Facebook\HackRouter\BaseRouter;
use Facebook\HackRouter\HttpMethod;

final class TestRouter<T> extends BaseRouter<T> {
  public function __construct(
    private ImmMap<string, T> $routes,
  ) {
  }

  <<__Override>>
  protected function getRoutes(
  ): ImmMap<HttpMethod, ImmMap<string, T>> {
    return ImmMap {
      HttpMethod::GET => $this->routes,
    };
  }
}
