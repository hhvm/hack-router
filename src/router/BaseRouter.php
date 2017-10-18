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

abstract class BaseRouter<+TResponder> {
  abstract protected function getRoutes(
  ): dict<HttpMethod, dict<string, TResponder>>;

  final public function routeRequest(
    HttpMethod $method,
    string $path,
  ): (TResponder, dict<string, string>) {
    $resolver = $this->getResolver();
    return $this->getResolver()->resolve(
      $method,
      $path,
    );
  }

  final public function routePsr7Request(
    \Psr\Http\Message\RequestInterface $request,
  ): (TResponder, dict<string, string>) {
    $method = HttpMethod::coerce($request->getMethod());
    if ($method === null) {
      throw new MethodNotAllowedException();
    }
    return $this->routeRequest(
      $method,
      $request->getUri()->getPath(),
    );
  }

  protected function getResolver(): IResolver<TResponder> {
    return new FastRouteResolver(
      $this->getRoutes(),
      null,
    );
  }
}
