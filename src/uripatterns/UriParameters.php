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

class UriParameters {
  protected ImmMap<string, UriPatternParameter> $specs;

  public function __construct(
    Traversable<UriPatternParameter> $spec_vector,
    protected ImmMap<string, string> $values,
  ) {
    $specs = Map { };
    foreach ($spec_vector as $spec) {
      $specs[$spec->getName()] = $spec;
    }
    $this->specs = $specs->immutable();
  }

  final protected function getSpec<T as UriPatternParameter>(
    classname<T> $class,
    string $name,
  ): T {
    $spec = $this->specs->at($name);
    invariant(
      $spec instanceof $class,
      'Expected %s to be a %s, got %s',
      $name,
      $class,
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

  ///// Convenience Accessors /////

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
