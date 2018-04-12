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

use namespace HH\Lib\C;

trait RequestParameterGetters {
  require extends RequestParametersBase;

  final public function getString(string $name): string {
    return $this->getSimpleTyped(StringRequestParameter::class, $name);
  }

  final public function getOptionalString(string $name): ?string {
    return $this->getSimpleTypedOptional(StringRequestParameter::class, $name);
  }

  final public function getInt(string $name): int {
    return $this->getSimpleTyped(IntRequestParameter::class, $name);
  }

  final public function getOptionalInt(string $name): ?int {
    return $this->getSimpleTypedOptional(IntRequestParameter::class, $name);
  }

  final public function getEnum<TValue>(
    /* HH_FIXME[2053] */
    classname<\HH\BuiltinEnum<TValue>> $class,
    string $name,
  ): TValue {
    $value = $this->getEnumImpl(
      $this->getRequiredSpec(EnumRequestParameter::class, $name),
      $class,
      $name,
    );
    return $class::assert($value);
  }

  final public function getOptionalEnum<TValue>(
    /* HH_FIXME[2053] */
    classname<\HH\BuiltinEnum<TValue>> $class,
    string $name,
  ): ?TValue {
    return $this->getEnumImpl(
      $this->getOptionalSpec(EnumRequestParameter::class, $name),
      $class,
      $name,
    );
  }

  final private function getEnumImpl<TValue>(
    EnumRequestParameter<TValue> $spec,
    /* HH_FIXME[2053] */
    classname<\HH\BuiltinEnum<TValue>> $class,
    string $name,
  ): ?TValue {
    invariant(
      $spec->getEnumName() === $class,
      'Expected %s to be a %s, actually a %s',
      $name,
      $class,
      $spec->getEnumName(),
    );
    if (!C\contains_key($this->values, $name)) {
      return null;
    }
    return $spec->assert($this->values[$name]);
  }
}
