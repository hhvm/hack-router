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


final class ParameterNode implements Node {
  public function __construct(private string $name, private ?string $regexp) {
  }

  public function getName(): string {
    return $this->name;
  }

  public function getRegexp(): ?string {
    return $this->regexp;
  }

  public function toStringForDebug(): string {
    $re = $this->getRegexp();
    if ($re === null) {
      return '{'.$this->getName().'}';
    }

    return \sprintf('{%s: #%s#}', $this->getName(), $this->getRegexp());
  }

  public function asRegexp(string $delimiter): string {
    $re = $this->getRegexp();
    if ($re === null) {
      $re = '[^/]+';
    }
    return '(?<'.\preg_quote($this->getName(), $delimiter).'>'.$re.')';
  }
}
