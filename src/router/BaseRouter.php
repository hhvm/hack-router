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
  ): ImmMap<HttpMethod, ImmMap<string, TResponder>>;

  protected function getCacheFilePath(): ?string {
    return null;
  }

  final public function routeRequest(
    HttpMethod $method,
    string $path,
  ): (TResponder, ImmMap<string, string>) {
    $resolver = $this->getResolver();
    list($responder, $params) = $resolver->resolve(
      $method,
      $path,
    );
    return tuple($responder, new ImmMap($params));
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

  protected function getResolver(): IResolver<TResponder> {
    return new FastRouteResolver(
      $this->getRoutes(),
      $this->getCacheFilePath(),
    );
  }
}
