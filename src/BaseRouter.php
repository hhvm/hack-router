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

abstract class BaseRouter<
  TBaseResponder,
  TGETResponder as TBaseResponder,
  TPOSTResponder as TBaseResponder
> {
  abstract protected function getGETRoutes(): ImmMap<string, TGETResponder>;
  abstract protected function getPOSTRoutes(): ImmMap<string, TPOSTResponder>;

  protected function getCacheFilePath(): ?string {
    return null;
  }

  final public function routeRequest(
    string $method,
    string $path,
  ): (classname<TBaseResponder>, ImmMap<string, string>) {
    $route = $this->getDispatcher()->dispatch(
      $method,
      $path,
    );
    switch ($route[0]) {
      case \FastRoute\Dispatcher::NOT_FOUND:
        throw new NotFoundException($method, $path);
      case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        throw new MethodNotAllowedException($method, $path);
      case \FastRoute\Dispatcher::FOUND:
        return tuple(
          $route[1],
          (new Map($route[2]))
            ->map($encoded ==> urldecode($encoded))
            ->toImmMap(),
        );
    }

    throw new UnknownException($route, $method, $path);
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
    foreach ($this->getGETRoutes() as $route => $responder) {
      $r->addRoute('GET', $route, $responder);
    }
    foreach ($this->getPOSTRoutes() as $route => $responder) {
      $r->addRoute('POST', $route, $responder);
    }
  }
}
