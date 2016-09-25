<?hh // strict
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

trait UriParametersGetters {
  require extends UriParametersBase;

  final public function getString(string $name): string {
    return $this->getSimpleTyped(UriPatternStringParameter::class, $name);
  }

  final public function getInt(string $name): int {
    return $this->getSimpleTyped(UriPatternIntParameter::class, $name);
  }

  final public function getEnum<TValue>(
    /* HH_FIXME[2053] */
    classname<\HH\BuiltinEnum<TValue>> $class,
    string $name,
  ): TValue {
    $spec = $this->getSpec(
      UriPatternEnumParameter::class,
      $name,
    );
    invariant(
      $spec->getEnumName() === $class,
      'Expected %s to be a %s, actually a %s',
      $name,
      $class,
      $spec->getEnumName(),
    );
    return $spec->assert($this->values->at($name));
  }
}
