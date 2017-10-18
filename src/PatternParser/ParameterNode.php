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

use namespace HH\Lib\Str;

final class ParameterNode implements Node {
  public function __construct(
    private string $name,
    private ?string $regexp,
  ) {
  }

  public function getName(): string {
    return $this->name;
  }

  public function getRegexp(): ?string {
    return $this->regexp;
  }

  public function _toStringForDebug(): string {
    $re = $this->getRegexp();
    if ($re === null) {
      return '{'.$this->getName().'}';
    }

    return sprintf(
      '{%s: #%s#}',
      $this->getName(),
      $this->getRegexp(),
    );
  }
}
