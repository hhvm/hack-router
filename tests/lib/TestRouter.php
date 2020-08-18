<?hh // strict
/*
 *  Copyright (c) 2015-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace Facebook\HackRouter\Tests;

use type Facebook\HackRouter\{
  BaseRouter,
  HttpMethod,
  IResolver,
};

final class TestRouter<T> extends BaseRouter<T> {
  public function __construct(
    private dict<string, T> $routes,
    private ?IResolver<T> $resolver = null,
  ) {
  }

  <<__Override>>
  protected function getRoutes(
  ): dict<HttpMethod, dict<string, T>> {
    return dict[
      HttpMethod::GET => $this->routes,
    ];
  }

  public function setResolver(IResolver<T> $resolver): this {
    $this->resolver = $resolver;
    return $this;
  }

  <<__Override>>
  protected function getResolver(): IResolver<T> {
    $r = $this->resolver;
    if ($r === null) {
      return parent::getResolver();
    }
    return $r;
  }
}
