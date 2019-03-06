<?hh // partial
/*
 *  Copyright (c) 2015-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace Facebook\HackRouter;

require_once (__DIR__.'/../vendor/hh_autoload.php');

use namespace HH\Lib\{C, Dict, Keyset, Str, Vec};

// Dump out a massive URI map for testing/benchmarking
final class RandomUriMapGenerator {
  // (pattern, dict<example url => data from example url>);
  const type TGeneratedExample = (string, dict<string, dict<string, string>>);
  const int TOP_LEVEL_COUNT = 500;

  public static function main(): void {
    $map = Vec\map(
      Vec\range(1, self::TOP_LEVEL_COUNT),
      $_ ==> self::generateExampleInner(0),
    )
      |> Vec\flatten($$)
      |> Vec\filter($$, $row ==> !Str\starts_with($row[0], '/{'))
      |> Vec\sort_by($$, $row ==> $row[0]);
    print (\json_encode($map, \JSON_PRETTY_PRINT)."\n");
  }

  private static function generateExampleInner(
    int $depth,
  ): vec<self::TGeneratedExample> {
    $base = self::generateExampleComponent();
    $child_free = \random_int(0, 5) <= $depth;
    if ($child_free) {
      return vec[$base];
    }

    $children = Vec\map(
      Vec\range(2, \random_int(2, \max(2, \intdiv(10, $depth + 1)))),
      $_ ==> self::generateExampleInner($depth + 1),
    )
      |> Vec\flatten($$)
      |> Vec\unique_by(
        $$,
        $row ==> {
          $pattern = $row[0];
          if (Str\starts_with($pattern, '/{')) {
            return Str\slice($pattern, 0, 4);
          }
          return $pattern;
        },
      );

    list($prefix, $base_examples) = $base;

    return Vec\map(
      $children,
      $child ==> {
        list($suffix, $child_examples) = $child;
        $examples = dict[];
        foreach ($base_examples as $base_uri => $base_data) {
          foreach ($child_examples as $child_uri => $child_data) {
            $examples[$base_uri.$child_uri] =
              Dict\merge($base_data, $child_data);
          }
        }
        return tuple($prefix.$suffix, $examples);
      },
    );
  }

  private static function randomAlnum(
    int $min_length,
    int $max_length,
  ): string {
    $alphabet = '0123456789abcdefghijklmnopqrstuvwxyz';
    $alphabet_max = Str\length($alphabet) - 1;
    $len = \random_int($min_length, $max_length);
    $out = '';
    for ($i = 0; $i < $len; ++$i) {
      $out .= $alphabet[\random_int(0, $alphabet_max)];
    }
    return $out;
  }

  // It's important that more specific regexps sort first
  const string INT_REGEXP_PREFIX = 'a_';
  const string DEFAULT_REGEXP_PREFIX = 'b_';

  private static function generateExampleComponent(): self::TGeneratedExample {
    switch (\random_int(0, 10)) {
      // Component with default regexp
      case 0:
        $name = self::DEFAULT_REGEXP_PREFIX.self::randomAlnum(5, 15);
        return tuple(
          '/{'.$name.'}/',
          Vec\fill(\random_int(1, 5), '')
          |> Keyset\map($$, $_ ==> self::randomAlnum(5, 15))
          |> Dict\pull($$, $v ==> dict[$name => $v], $v ==> '/'.$v.'/'),
        );
      case 1:
        // Component with int regexp
        $name = self::INT_REGEXP_PREFIX.self::randomAlnum(5, 15);
        return tuple(
          '/{'.$name.':\\d+}/',
          Vec\fill(\random_int(1, 5), '')
          |> Keyset\map($$, $_ ==> (string)\random_int(1, \PHP_INT_MAX))
          |> Dict\pull($$, $v ==> dict[$name => $v], $v ==> '/'.$v.'/'),
        );
      // Literal
      default:
        $value = self::randomAlnum(5, 15);
        return tuple($value, dict[$value => dict[]]);
    }
  }
}

RandomUriMapGenerator::main();
