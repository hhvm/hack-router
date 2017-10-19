<?hh //strict
/*
 *  Copyright (c) 2015-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the BSD-style license found in the
 *  LICENSE file in the root directory of this source tree. An additional grant
 *  of patent rights can be found in the PATENTS file in the same directory.
 *
 */

namespace Facebook\HackRouter\PrefixMatching;

use type Facebook\HackRouter\PatternParser\{
  LiteralNode,
  Node,
  Parser,
};
use namespace HH\Lib\{C, Dict, Keyset, Str, Vec};

final class PrefixMap<T> {
  public function __construct(
    private dict<string, T> $literals,
    private dict<string, PrefixMap<T>> $prefixes,
    private dict<string, PrefixMapOrResponder<T>> $regexps,
  ) {
  }

  public static function fromFlatMap(
    dict<string, T> $map,
  ): PrefixMap<T> {
    $entries = Vec\map_with_key(
      $map,
      ($pattern, $responder) ==> tuple(
        Parser::parse($pattern)->getChildren(),
        $responder,
      ),
    );

    return self::fromFlatMapImpl($entries);
  }

  private static function fromFlatMapImpl(
    vec<(vec<Node>, T)> $entries,
  ): PrefixMap<T> {
    $literals = dict[];
    $prefixes = vec[];
    $regexps = vec[];
    foreach ($entries as list($nodes, $responder)) {
      $node = C\first($nodes);
      if ($node === null) {
        $literals[''] = $responder;
        continue;
      }
      $nodes = Vec\drop($nodes, 1);
      if ($node instanceof LiteralNode) {
        if (C\is_empty($nodes)) {
          $literals[$node->getText()] = $responder;
        } else {
          $prefixes[] = tuple($node->getText(), $nodes, $responder);
        }
      } else {
        $regexps[] = tuple('#'.$node->asRegexp('#').'#', $nodes, $responder);
      }
    }

    $by_first = $literals;
    $grouped = self::groupByCommonPrefix(Keyset\keys($literals));
    $literals = dict[];
    foreach ($grouped as $prefix => $keys) {
      if (C\count($keys) === 1) {
        $key = C\onlyx($keys);
        $literals[$key] = $by_first[$key];
        continue;
      }

      $prefixes = Vec\concat(
        $prefixes,
        Vec\map($keys, $key ==> tuple($key, vec[], $by_first[$key])),
      );
    }

    $by_first = Dict\group_by($prefixes, $entry ==> $entry[0]);
    $grouped = self::groupByCommonPrefix(Keyset\keys($by_first));
    $prefixes = Dict\map_with_key(
      $grouped,
      ($prefix, $keys) ==> Vec\map(
        $keys,
        $key ==> Vec\map(
          $by_first[$key],
          $row ==> {
            list($text, $nodes, $responder) = $row;
            if ($text === $prefix) {
              return tuple($nodes, $responder);
            }
            $suffix = Str\strip_prefix($text, $prefix);
            return tuple(
              Vec\concat(
                vec[new LiteralNode($suffix)],
                $nodes,
              ),
              $responder,
            );
          }
        )
      ) |> Vec\flatten($$) |> self::fromFlatMapImpl($$)
    );

    $by_first = Dict\group_by($regexps, $entry ==> $entry[0]);
    $regexps = Dict\map(
      $by_first,
      $entries ==> $entries
        |> Vec\map(
          $$,
          $entry ==> tuple($entry[1], $entry[2]),
        )
        |> C\count($$) === 1
          ? new PrefixMapOrResponder(null, C\onlyx($$)[1])
          : new PrefixMapOrResponder(self::fromFlatMapImpl($$), null)
    );

    return new self($literals, $prefixes, $regexps);
  }

  const int MIN_PREFIX_LENGTH = 2;

  public static function groupByCommonPrefix(
    keyset<string> $keys,
  ): dict<string, keyset<string>> {
    $by_prefix = $keys
      |> Dict\group_by(
        $$,
        $key ==> Str\slice($key, 0, self::MIN_PREFIX_LENGTH),
      )
      |> Dict\map(
        $$,
        $keys ==> keyset($keys),
      );
    foreach ($by_prefix as $prefix => $keys) {
      if (C\count($keys) === 1) {
        unset($by_prefix[$prefix]);
        $key = C\onlyx($keys);
        $by_prefix[$key] = $keys;
        continue;
      }
      $max_len = Vec\map($keys, $k ==> Str\length($k)) |> max($$);
      $new_prefix = null;
      for ($len = self::MIN_PREFIX_LENGTH + 1; $len < $max_len - 1; ++$len) {
        $new_prefixes = Keyset\map(
          $keys,
          $key ==> Str\slice($key, 0, $len),
        );
        if (C\count($new_prefixes) > 1) {
          break;
        }
        $new_prefix = C\onlyx($new_prefixes);
      }
      if ($new_prefix === null) {
        continue;
      }
      unset($by_prefix[$prefix]);
      $by_prefix[$new_prefix] = $keys;
    }
    return $by_prefix;
  }

  public function getSerializable(): mixed where T as string {
    return dict[
      'literals' => $this->literals,
      'prefixes' => Dict\map($this->prefixes, $it ==> $it->getSerializable()),
      'regexps' => Dict\map($this->regexps, $it ==> $it->getSerializable()),
    ] |> Dict\filter($$, $it ==> !C\is_empty($it));
  }
}
