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

use namespace HH\Lib\{C, Dict, Str};
use type Facebook\HackRouter\PrefixMatching\PrefixMap;

final class PrefixMatchingResolver<+TResponder>
  implements IResolver<TResponder> {
  public function __construct(
    private dict<HttpMethod, PrefixMap<TResponder>> $map,
  ) {
  }

  public static function fromFlatMap(
    dict<HttpMethod, dict<string, TResponder>> $map,
  ): PrefixMatchingResolver<TResponder> {
    $map = Dict\map($map, $flat_map ==> PrefixMap::fromFlatMap($flat_map));
    return new self($map);
  }

  public function resolve(
    HttpMethod $method,
    string $path,
  ): (TResponder, dict<string, string>) {
    $map = $this->map[$method] ?? null;
    if ($map === null) {
      throw new NotFoundException();
    }

    return $this->resolveWithMap($path, $map);
  }

  private function resolveWithMap(
    string $path,
    PrefixMap<TResponder> $map,
  ): (TResponder, dict<string, string>) {
    $literals = $map->getLiterals();
    if (C\contains_key($literals, $path)) {
      return tuple($literals[$path], dict[]);
    }

    $prefixes = $map->getPrefixes();
    if (!C\is_empty($prefixes)) {
      $prefix_len = Str\length(C\first_keyx($prefixes));
      $prefix = Str\slice($path, 0, $prefix_len);
      if (C\contains_key($prefixes, $prefix)) {
        return $this->resolveWithMap(
          Str\strip_prefix($path, $prefix),
          $prefixes[$prefix],
        );
      }
    }

    $regexps = $map->getRegexps();
    foreach ($regexps as $regexp => $_sub_map) {
      $pattern = '#^'.$regexp.'#';
      $matches = varray[];

      if (\preg_match_with_matches($pattern, $path, inout $matches) !== 1) {
        continue;
      }
      $matched = $matches[0];
      $remaining = Str\strip_prefix($path, $matched);

      $data = Dict\filter_keys($matches, $key ==> $key is string);
      $sub = $regexps[$regexp];

      if ($sub->isResponder()) {
        if ($remaining === '') {
          return tuple($sub->getResponder(), $data);
        }
        continue;
      }
      try {
        list($responder, $sub_data) =
          $this->resolveWithMap($remaining, $sub->getMap());
      } catch (NotFoundException $_) {
        continue;
      }
      return tuple($responder, Dict\merge($data, $sub_data));
    }

    throw new NotFoundException();
  }
}
