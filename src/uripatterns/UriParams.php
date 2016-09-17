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

class UriParams {
  public function __construct(
    protected ImmMap<string, UriPatternParameter> $spec,
    protected ImmMap<string, string> $values,
  ) {
  }

  final protected function getSpec<T as UriPatternParameter>(
    classname<T> $class,
    string $name,
  ): T {
    $spec = $this->spec->at($name);
    invariant(
      $spec instanceof $class,
      'Expected %s to be a %s, got %s',
      $name,
      get_class($spec),
    );
    return $spec;
  }

  final protected function getSimpleTyped<T>(
    classname<UriPatternTypedParameter<T>> $class,
    string $name,
  ): T {
    $spec = $this->getSpec($class, $name);
    $value = $this->values->at($name);
    return $spec->assert($value);
  }

  final public function getString(string $name): string {
    return $this->getSimpleTyped(UriPatternStringParameter::class, $name);
  }

  final public function getInt(string $name): int {
    return $this->getSimpleTyped(UriPatternIntParameter::class, $name);
  }
}
