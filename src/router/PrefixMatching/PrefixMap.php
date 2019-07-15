<?hh // strict
/*
 *  Copyright (c) 2015-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace Facebook\HackRouter\PrefixMatching;

use type Facebook\HackRouter\PatternParser\{
  LiteralNode,
  Node,
  ParameterNode,
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

  public function getLiterals(): dict<string, T> {
    return $this->literals;
  }

  public function getPrefixes(): dict<string, PrefixMap<T>> {
    return $this->prefixes;
  }

  public function getRegexps(): dict<string, PrefixMapOrResponder<T>> {
    return $this->regexps;
  }

  public static function fromFlatMap(dict<string, T> $map): PrefixMap<T> {
    $entries = Vec\map_with_key(
      $map,
      ($pattern, $responder) ==>
        tuple(Parser::parse($pattern)->getChildren(), $responder),
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
      if (C\is_empty($nodes)) {
        $literals[''] = $responder;
        continue;
      }
      $node = C\firstx($nodes);
      $nodes = Vec\drop($nodes, 1);
      if ($node is LiteralNode) {
        if (C\is_empty($nodes)) {
          $literals[$node->getText()] = $responder;
        } else {
          $prefixes[] = tuple($node->getText(), $nodes, $responder);
        }
        continue;
      }

      if ($node is ParameterNode && $node->getRegexp() === null) {
        $next = C\first($nodes);
        if (
          $next is LiteralNode && Str\starts_with($next->getText(), '/')
        ) {
          $regexps[] = tuple($node->asRegexp('#'), $nodes, $responder);
          continue;
        }
      }
      $regexps[] = tuple(
        Vec\concat(vec[$node], $nodes)
        |> Vec\map($$, $n ==> $n->asRegexp('#'))
        |> Str\join($$, ''),
        vec[],
        $responder,
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
              Vec\concat(vec[new LiteralNode($suffix)], $nodes),
              $responder,
            );
          },
        ),
      )
        |> Vec\flatten($$)
        |> self::fromFlatMapImpl($$),
    );

    $by_first = Dict\group_by($regexps, $entry ==> $entry[0]);
    $regexps = dict[];
    foreach ($by_first as $first => $entries) {
      if (C\count($entries) === 1) {
        list($_, $nodes, $responder) = C\onlyx($entries);
        $rest = Str\join(Vec\map($nodes, $n ==> $n->asRegexp('#')), '');
        $regexps[$first.$rest] = new PrefixMapOrResponder(null, $responder);
        continue;
      }
      $regexps[$first] = new PrefixMapOrResponder(
        self::fromFlatMapImpl(Vec\map($entries, $e ==> tuple($e[1], $e[2]))),
        null,
      );
    }

    return new self($literals, $prefixes, $regexps);
  }

  private static function groupByCommonPrefix(
    keyset<string> $keys,
  ): dict<string, keyset<string>> {
    if (C\is_empty($keys)) {
      return dict[];
    }
    $lens = Vec\map($keys, $key ==> Str\length($key));
    $min = \min($lens);
    invariant($min !== 0, "Shouldn't have 0-length prefixes");
    return $keys
      |> Dict\group_by($$, $key ==> Str\slice($key, 0, $min))
      |> Dict\map($$, $vec ==> keyset($vec));
  }

  public function getSerializable(): mixed where T as string {
    return dict[
      'literals' => $this->literals,
      'prefixes' => Dict\map($this->prefixes, $it ==> $it->getSerializable()),
      'regexps' => Dict\map($this->regexps, $it ==> $it->getSerializable()),
    ]
      |> Dict\filter($$, $it ==> !C\is_empty($it));
  }
}
