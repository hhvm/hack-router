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

namespace Facebook\HackRouter;

trait UriBuilderSetters {
  require extends UriBuilderBase;

  final public function setString(string $name, string $value): this {
    return $this->setValue(StringRequestParameter::class, $name, $value);
  }

  final public function setInt(string $name, int $value): this {
    return $this->setValue(IntRequestParameter::class, $name, $value);
  }

  final public function setEnum<T>(
    /* HH_FIXME[2053] */ classname<\HH\BuiltinEnum<T>> $class,
    string $name,
    T $value,
  ): this {
    $spec = $this->parameters[$name] ?? null;
    if ($spec && $spec instanceof EnumRequestParameter) {
      // Null case is handled by standard checks in setValue()
      $expected_class = $spec->getEnumName();
      invariant(
        $class === $expected_class,
        'Parameter "%s" is a %s, not a %s',
        $name,
        $expected_class,
        $class,
      );
    }
    return $this->setValue(
      EnumRequestParameter::class,
      $name,
      $class::assert($value),
    );
  }
}
