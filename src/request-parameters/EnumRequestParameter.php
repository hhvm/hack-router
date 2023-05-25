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

// HHAST_IGNORE_ERROR[FinalOrAbstractClass] maybe extended outside this library.
class EnumRequestParameter<T> extends TypedUriParameter<T> {
  public function __construct(
    private \HH\enumname<T> $enumClass,
    string $name,
  ) {
    parent::__construct($name);
  }

  final public function getEnumName(): \HH\enumname<T> {
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
    $sub_fragments = Vec\map($values, $value ==> \preg_quote((string)$value));
    return '(?:'.Str\join($sub_fragments, '|').')';
  }
}
