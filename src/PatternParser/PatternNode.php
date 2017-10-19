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

namespace Facebook\HackRouter\PatternParser;

use namespace HH\Lib\{Keyset, Str, Vec};

final class PatternNode implements Node {
  public function __construct(private vec<Node> $children) {
  }

  public function getChildren(): vec<Node> {
    return $this->children;
  }

  public function _toStringForDebug(): string {
    return $this->children
      |> Vec\map($$, $child ==> $child->_toStringForDebug())
      |> Str\join($$, ', ')
      |> '['.$$.']';
  }

  public function asRegexp(string $delimiter): string {
    return $this->children
      |> Vec\map($$, $child ==> $child->asRegexp($delimiter))
      |> Str\join($$, '');
  }
}
