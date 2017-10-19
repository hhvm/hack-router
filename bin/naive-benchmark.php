<?hh
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

require_once(__DIR__.'/../vendor/hh_autoload.php');

use namespace HH\Lib\{C, Dict, Keyset, Math, Str, Vec};

final class NaiveBenchmark {
  public static function main(): void {
    printf(
      "Map has %d entries and %d URIs\n",
      C\count(self::getMap()),
      self::getMap()
        |> Vec\map($$, $row ==> C\count($row[1]))
        |> Math\sum($$),
    );
    $impls = self::getImplementations();
    self::testImplementations('Cold', $impls);
    self::testImplementations('Warm', $impls);
  }
  private static function testImplementations(
    string $run_name,
    dict<string, (function():IResolver<string>)> $impls,
  ): void {
    foreach ($impls as $name => $impl) {
      printf("%s run for %s...\n", $run_name, $name);
      list($init, $lookup) = self::testImplementation($name, $impl);
      printf("... done (init: %0.02f, lookup: %0.02f)\n", $init, $lookup);
    }
  }

  private static function testImplementation(
    string $name,
    (function():IResolver<string>) $impl,
  ): (float, float) {
    $create_start = microtime(true);
    $impl = $impl();
    $create_time = microtime(true) - $create_start;

    $map = self::getMap();
    $resolve_time = 0.0;
    foreach ($map as $row) {
      list($expected_responder, $examples) = $row;
      foreach ($examples as $uri => $expected_data) {
        $resolve_start = microtime(true);
        try {
          list($responder, $data) = $impl->resolve(HttpMethod::GET, $uri);
        } catch (NotFoundException $e) {
          fprintf(
            STDERR,
            "%s failed to resolve %s - expected %s\n",
            $name,
            $uri,
            $expected_responder,
          );
          throw $e;
        }
        $resolve_time += microtime(true) - $resolve_start;

        invariant(
          $responder === $expected_responder,
          "For resolver %s:\nFor path %s:\n  Expected: %s\n  Actual: %s\n",
          $name,
          $uri,
          $expected_responder,
          $responder,
        );
        $pretty_data = $dict ==> var_export($dict, true)
          |> Str\split($$, "\n")
          |> Vec\map($$, $line ==> '    '.$line)
          |> Str\join($$, "\n");
        invariant(
          $data === $expected_data,
          "For resolver: %s\nFor path %s:\n  Expected data:\n%s\n  Actual data:\n%s\n",
          $name,
          $uri,
          $pretty_data($expected_data),
          $pretty_data($data),
        );
      }
    }

    return tuple($create_time, $resolve_time);
  }

  <<__Memoize>>
  private static function getMap(
  ): vec<(string, dict<string, dict<string, string>>)> {
    return json_decode(
      file_get_contents(__DIR__.'/../data/big-random-map.json'),
      /* assoc = */ true,
      /* depth = [default] */ 512,
      JSON_FB_HACK_ARRAYS,
    );
  }

  private static function getImplementations(
  ): dict<string, (function():IResolver<string>)> {
    $fast_route_cache = tempnam(sys_get_temp_dir(), 'frcache');
    unlink($fast_route_cache);

    $map = dict[
      HttpMethod::GET => dict(Keyset\map(self::getMap(), $row ==> $row[0])),
    ];

    return dict[
      'simple regexp' =>
        () ==> new SimpleRegexpResolver($map),
      'uncached fastroute' =>
        () ==> new FastRouteResolver($map, null),
      'cached fastroute' =>
        () ==> new FastRouteResolver($map, $fast_route_cache),
      'uncached prefix match' =>
        () ==> new PrefixMatchingResolver($map),
    ];
  }
}

NaiveBenchmark::main();
