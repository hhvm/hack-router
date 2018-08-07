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

abstract class RequestParameter {
  /** Convert to T or throw an exception if failed. */
  abstract public function assert(string $input): mixed;

  public function __construct(private string $name) {
  }

  final public function getName(): string {
    return $this->name;
  }
}
