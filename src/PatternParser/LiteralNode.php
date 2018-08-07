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

final class LiteralNode implements Node {
  public function __construct(private string $text) {
    invariant($text !== '', 'No empty literal nodes');
  }

  public function getText(): string {
    return $this->text;
  }

  public function toStringForDebug(): string {
    return \var_export($this->getText(), true);
  }

  public function asRegexp(string $delimiter): string {
    return \preg_quote($this->getText(), $delimiter);
  }
}
