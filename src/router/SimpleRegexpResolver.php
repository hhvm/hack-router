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

use namespace HH\Lib\{C, Dict};

final class SimpleRegexpResolver<+TResponder> implements IResolver<TResponder>{
  private dict<HttpMethod, dict<string, (TResponder, keyset<string>)>> $map;
  public function __construct(
    dict<HttpMethod, dict<string, TResponder>> $map,
  ) {
    $this->map = Dict\map(
      $map,
      $routes ==> $routes
      |> Dict\map_with_key(
        $$,
        ($fastroute, $responder) ==> tuple(
          $responder,
          self::getParameterNames($fastroute),
        ),
      )
      |> Dict\map_keys(
        $$,
        $fastroute ==> self::fastRouteToRegexp($fastroute),
      ),
    );
  }

  public function resolve(
    HttpMethod $method,
    string $path,
  ): (TResponder, dict<string, string>) {
    if (!C\contains_key($this->map, $method)) {
      throw new MethodNotAllowedException();
    }
    $map = $this->map[$method];
    foreach ($map as $regexp => list($responder, $params)) {
      $matches = [];
      if (preg_match($regexp, $path, $matches) !== 1) {
        continue;
      }
      $ret = tuple(
        $responder,
        Dict\filter_keys(
          $matches,
          $key ==> C\contains_key($params, $key),
        ),
      );
      return $ret;
    }
    throw new NotFoundException();
  }

  private static function fastRouteToRegexp(
    string $fastroute,
  ): string {
    $pattern = PatternParser\Parser::parse($fastroute);
    return '#^'.$pattern->asRegexp('#').'$#';
  }

  private static function getParameterNames(
    string $fastroute,
  ): keyset<string> {
    $pattern = PatternParser\Parser::parse($fastroute);
    return $pattern->getParameterNames();
  }
}
