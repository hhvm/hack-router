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

use Facebook\HackRouter\GETOnlyRouter;

final class TestRouter<T> extends GETOnlyRouter<T> {
  public function __construct(
    private ImmMap<string, T> $routes,
  ) {
  }

  protected function getGETRoutes(): ImmMap<string, T> {
    return $this->routes;
  }
}
