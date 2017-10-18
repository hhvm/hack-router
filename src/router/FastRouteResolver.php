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

use namespace HH\Lib\Dict;

final class FastRouteResolver<+TResponder> implements IResolver<TResponder>{
  private \FastRoute\Dispatcher $impl;

  public function __construct(
    ImmMap<HttpMethod, ImmMap<string, TResponder>> $map,
    ?string $cache_file,
  ) {
    if ($cache_file !== null) {
      $factory = fun('\FastRoute\cachedDispatcher');
      $options = shape(
        'cacheFile' => $cache_file,
        'cacheDisabled' => false,
      );
    } else {
      $factory = fun('\FastRoute\simpleDispatcher');
      $options = shape();
    }

    $this->impl = $factory(
      $rc ==> self::addRoutesToCollector($map, $rc),
      $options,
    );
  }

  public function resolve(
    HttpMethod $method,
    string $path,
  ): (TResponder, dict<string, string>) {
    $impl= $this->impl;
    $route = $impl->dispatch(
      (string) $method,
      $path,
    );
    switch ($route[0]) {
      case \FastRoute\Dispatcher::NOT_FOUND:
        throw new NotFoundException();
      case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        throw new MethodNotAllowedException();
      case \FastRoute\Dispatcher::FOUND:
        return tuple(
          $route[1],
          Dict\map(
            $route[2],
            $encoded ==> urldecode($encoded),
          ),
        );
    }

    throw new UnknownRouterException($route);
  }

  private static function addRoutesToCollector(
    ImmMap<HttpMethod, ImmMap<string, TResponder>> $map,
    \FastRoute\RouteCollector $r,
  ): void {
    foreach ($map as $method => $routes) {
      foreach ($routes as $route => $responder) {
        $r->addRoute($method, $route, $responder);
      }
    }
  }
}
