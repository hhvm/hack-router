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
use function Facebook\AutoloadMap\Generated\is_dev;

abstract class BaseRouter<+TResponder> {
  abstract protected function getRoutes(
  ): ImmMap<HttpMethod, ImmMap<string, TResponder>>;

  final public function routeRequest(
    HttpMethod $method,
    string $path,
  ): (TResponder, ImmMap<string, string>) {
    $resolver = $this->getResolver();
    try {
      list($responder, $data) = $resolver->resolve($method, $path);
      return tuple($responder, new ImmMap($data));
    } catch (NotFoundException $e) {
      foreach (HttpMethod::getValues() as $next) {
        if ($next === $method) {
          continue;
        }
        try {
          list($responder, $data) = $resolver->resolve($next, $path);
          if ($method === HttpMethod::HEAD && $next === HttpMethod::GET) {
            return tuple($responder, new ImmMap($data));
          }
          throw new MethodNotAllowedException();
        } catch (NotFoundException $_) {
          continue;
        }
      }
      throw $e;
    }
  }

  final public function routePsr7Request(
    \Psr\Http\Message\RequestInterface $request,
  ): (TResponder, ImmMap<string, string>) {
    $method = HttpMethod::coerce($request->getMethod());
    if ($method === null) {
      throw new MethodNotAllowedException();
    }
    return $this->routeRequest($method, $request->getUri()->getPath());
  }

  protected function getResolver(): IResolver<TResponder> {
    // Don't use <<__Memoize>> because that can be surprising with subclassing
    static $resolver = null;
    if ($resolver !== null) {
      return $resolver;
    }


    if (is_dev()) {
      $routes = null;
    } else {
      $routes = apc_fetch(__FILE__.'/cache');
      if ($routes === false) {
        $routes = null;
      }
    }

    if ($routes === null) {
      $routes = Dict\map(
        $this->getRoutes(),
        $method_routes ==> PrefixMatching\PrefixMap::fromFlatMap(
          dict($method_routes),
        ),
      );

      if (!is_dev()) {
        apc_store(__FILE__.'/cache', $routes);
      }
    }
    return new PrefixMatchingResolver($routes);
  }
}
