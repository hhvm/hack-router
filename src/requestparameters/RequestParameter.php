<?hh //strict
/*
 *  Copyright (c) 2015, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the BSD-style license found in the
 *  LICENSE file in the root directory of this source tree. An additional grant
 *  of patent rights can be found in the PATENTS file in the same directory.
 *
 */

namespace Facebook\HackRouter;

abstract class RequestParameter implements UriPatternPart {
  /** Convert to T or throw an exception if failed. */
  abstract public function assert(string $input): mixed;

  /** Partial regexp fragment used to define pattern.
   *
   * If null, any value is accepted. For example:
   *  - a string parameter should return `null`
   *  - an int parameter should return `"\d+"`
   *
   * Capturing groups are not permitted.
   */
  abstract public function getRegExpFragment(): ?string;

  final public function getFastRouteFragment(): string {
    $re = $this->getRegExpFragment();
    if ($re === null) {
      return '{'.$this->name.'}';
    }
    return '{'.$this->name.':'.$re.'}';
  }

  public function __construct(
    private string $name,
  ) {
  }

  final public function getName(): string {
    return $this->name;
  }
}
