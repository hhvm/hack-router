<?hh // strict
/*
 *  Copyright (c) 2015-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace Facebook\HackRouter;

use namespace HH\Lib\{C, Dict};

final class SimpleRegexpResolver<+TResponder> implements IResolver<TResponder> {
  private dict<HttpMethod, dict<string, TResponder>> $map;
  public function __construct(dict<HttpMethod, dict<string, TResponder>> $map) {
    $this->map = Dict\map(
      $map,
      $routes ==> Dict\map_keys(
        $routes,
        $fastroute ==> self::fastRouteToRegexp($fastroute),
      ),
    );
  }

  public function resolve(
    HttpMethod $method,
    string $path,
  ): (TResponder, dict<string, string>) {
    if (!C\contains_key($this->map, $method)) {
      throw new NotFoundException();
    }
    $map = $this->map[$method];
    foreach ($map as $regexp => $responder) {
      $matches = varray[];
      if (\preg_match_with_matches($regexp, $path, inout $matches) !== 1) {
        continue;
      }
      $ret =
        tuple($responder, Dict\filter_keys($matches, $key ==> $key is string));
      return $ret;
    }
    throw new NotFoundException();
  }

  private static function fastRouteToRegexp(string $fastroute): string {
    $pattern = PatternParser\Parser::parse($fastroute);
    return '#^'.$pattern->asRegexp('#').'$#';
  }
}
