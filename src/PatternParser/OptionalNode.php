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

final class OptionalNode implements Node {
  public function __construct(private PatternNode $pattern) {
  }

  public function getPattern(): PatternNode {
    return $this->pattern;
  }

  public function _toStringForDebug(): string {
    return '?'.$this->pattern->_toStringForDebug();
  }

  public function asRegexp(string $delimiter): string {
    return '(?:'.$this->pattern->asRegexp($delimiter).')?';
  }
}
