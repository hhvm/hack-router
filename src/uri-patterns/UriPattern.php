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

// Non-final so you can extend it with additional convenience
// methods.
class UriPattern implements HasFastRouteFragment {
  private Vector<UriPatternPart> $parts = Vector {};

  final public function appendPart(UriPatternPart $part): this {
    $this->parts[] = $part;
    return $this;
  }

  final public function getFastRouteFragment(): string {
    $fragments = $this->parts->map($part ==> $part->getFastRouteFragment());
    return \implode('', $fragments);
  }

  final public function getParts(): ImmVector<UriPatternPart> {
    return $this->parts->immutable();
  }

  final public function getParameters(): ImmVector<UriParameter> {
    return $this
      ->parts
      ->filter($x ==> $x is UriParameter)
      ->map(
        $x ==> {
          assert($x is UriParameter);
          return $x;
        },
      )
      ->immutable();
  }

  ///// Convenience Methods /////

  final public function literal(string $part): this {
    return $this->appendPart(new UriPatternLiteral($part));
  }

  final public function slash(): this {
    return $this->literal('/');
  }

  final public function string(string $name): this {
    return $this->appendPart(new StringRequestParameter(
      StringRequestParameterSlashes::WITHOUT_SLASHES,
      $name,
    ));
  }

  final public function stringWithSlashes(string $name): this {
    return $this->appendPart(new StringRequestParameter(
      StringRequestParameterSlashes::ALLOW_SLASHES,
      $name,
    ));
  }

  final public function int(string $name): this {
    return $this->appendPart(new IntRequestParameter($name));
  }

  final public function enum<T>(
    /* HH_FIXME[2053] \HH\BuiltinEnum is an implementation detail */
    classname<\HH\BuiltinEnum<T>> $enum_class,
    string $name,
  ): this {
    return $this->appendPart(new EnumRequestParameter($enum_class, $name));
  }
}
