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

abstract class BaseRouter<TResponder> {
  abstract protected function getRoutes(
  ): ImmMap<HttpMethod, ImmMap<string, TResponder>>;

  protected function getCacheFilePath(): ?string {
    return null;
  }

  final public function routeRequest(
    HttpMethod $method,
    string $path,
  ): (TResponder, ImmMap<string, string>) {
    $route = $this->getDispatcher()->dispatch(
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
          (new Map($route[2]))
            ->map($encoded ==> urldecode($encoded))
            ->toImmMap(),
        );
    }

    throw new UnknownRouterException($route);
  }

  final public function routePsr7Request(
    \Psr\Http\Message\RequestInterface $request,
  ): (TResponder, ImmMap<string, string>) {
    $method = HttpMethod::coerce($request->getMethod());
    if ($method === null) {
      throw new MethodNotAllowedException();
    }
    return $this->routeRequest(
      $method,
      $request->getUri()->getPath(),
    );
  }

  final private function getDispatcher(): \FastRoute\Dispatcher {
    $cache_file = $this->getCacheFilePath();
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

    return $factory(
      $rc ==> $this->addRoutesToCollector($rc),
      $options,
    );
  }

  final private function addRoutesToCollector(
    \FastRoute\RouteCollector $r,
  ): void {
    foreach ($this->getRoutes() as $method => $routes) {
      foreach ($routes as $route => $responder) {
        $r->addRoute($method, $route, $responder);
      }
    }
  }
}
