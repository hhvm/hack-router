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

// Non-final so you can do enum-specific derivatives
class UriPatternEnumParameter<T>
extends UriPatternParameter<T> {
  public function __construct(
    /* HH_FIXME[2053] \HH\BuiltinEnum is dark internal magic :p */
    private classname<\HH\BuiltinEnum<T>> $enumClass,
    string $name,
  ) {
    parent::__construct($name);
  }


  <<__Override>>
  public function assert(string $input): T {
    $class = $this->enumClass;
    return $class::assert($input);
  }

  <<__Override>>
  public function getRegExpFragment(): ?string {
    $class = $this->enumClass;
    $values = (new ImmVector($class::getValues()));
    $sub_fragments = $values->map($value ==> preg_quote($value));
    return '(?:'.implode('|', $sub_fragments).')';
  }
}
