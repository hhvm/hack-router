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

final class OptionalNode implements Node {
  public function __construct(private PatternNode $pattern) {
  }

  public function getPattern(): PatternNode {
    return $this->pattern;
  }

  public function toStringForDebug(): string {
    return '?'.$this->pattern->toStringForDebug();
  }

  public function asRegexp(string $delimiter): string {
    return '(?:'.$this->pattern->asRegexp($delimiter).')?';
  }
}
