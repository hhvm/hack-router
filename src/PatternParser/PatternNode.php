<?hh // strict
/*
 *  Copyright (c) 2015-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace Facebook\HackRouter\PatternParser;

use namespace HH\Lib\{Str, Vec};

final class PatternNode implements Node {
  public function __construct(private vec<Node> $children) {
  }

  public function getChildren(): vec<Node> {
    return $this->children;
  }

  public function toStringForDebug(): string {
    return $this->children
      |> Vec\map($$, $child ==> $child->toStringForDebug())
      |> Str\join($$, ', ')
      |> '['.$$.']';
  }

  public function asRegexp(string $delimiter): string {
    return $this->children
      |> Vec\map($$, $child ==> $child->asRegexp($delimiter))
      |> Str\join($$, '');
  }
}
