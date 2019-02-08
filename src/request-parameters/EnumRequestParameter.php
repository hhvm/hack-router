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

use namespace HH\Lib\{Str, Vec};

class EnumRequestParameter<T> extends TypedUriParameter<T> {
  public function __construct(
    /* HH_FIXME[2053] */
    private classname<\HH\BuiltinEnum<T>> $enumClass,
    string $name,
  ) {
    parent::__construct($name);
  }

  /* HH_FIXME[2053] */
  final public function getEnumName(): classname<\HH\BuiltinEnum<T>> {
    return $this->enumClass;
  }

  <<__Override>>
  final public function getUriFragment(T $value): string {
    $class = $this->enumClass;
    return (string)$class::assert($value);
  }

  <<__Override>>
  public function assert(string $input): T {
    $class = $this->enumClass;
    return $class::assert($input);
  }

  <<__Override>>
  public function getRegExpFragment(): ?string {
    $class = $this->enumClass;
    $values = $class::getValues();
    $sub_fragments = Vec\map($values, $value ==> \preg_quote((string) $value));
    return '(?:'.Str\join($sub_fragments, '|').')';
  }
}
