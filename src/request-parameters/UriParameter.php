<?hh // strict
/*
 *  Copyright (c) 2015-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace Facebook\HackRouter;

abstract class UriParameter extends RequestParameter implements UriPatternPart {
  abstract public function getRegExpFragment(): ?string;

  final public function getFastRouteFragment(): string {
    $name = $this->getName();
    $re = $this->getRegExpFragment();
    if ($re === null) {
      return '{'.$name.'}';
    }
    return '{'.$name.':'.$re.'}';
  }
}
