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

use namespace HH\Lib\{C, Dict};

abstract class RequestParametersBase {
  private dict<string, RequestParameter> $requiredSpecs;
  private dict<string, RequestParameter> $optionalSpecs;

  protected ImmMap<string, string> $values;

  public function __construct(
    Traversable<RequestParameter> $required_specs,
    Traversable<RequestParameter> $optional_specs,
    KeyedTraversable<string, string> $values,
  ) {
    $this->values = new ImmMap($values);
    $spec_vector_to_map = (
      Traversable<RequestParameter> $specs
    ) ==> Dict\pull($specs, $it ==> $it, $it ==> $it->getName());

    $this->requiredSpecs = $spec_vector_to_map($required_specs);
    $this->optionalSpecs = $spec_vector_to_map($optional_specs);
  }

  final protected function getRequiredSpec<T as RequestParameter>(
    classname<T> $class,
    string $name,
  ): T {
    invariant(
      C\contains_key($this->requiredSpecs, $name),
      '%s is not a required parameter',
      $name,
    );
    return self::getSpec($this->requiredSpecs, $class, $name);
  }

  final protected function getOptionalSpec<T as RequestParameter>(
    classname<T> $class,
    string $name,
  ): T {
    invariant(
      C\contains_key($this->optionalSpecs, $name),
      '%s is not an optional parameter',
      $name,
    );
    return self::getSpec($this->optionalSpecs, $class, $name);
  }

  final private static function getSpec<T as RequestParameter>(
    dict<string, RequestParameter> $specs,
    classname<T> $class,
    string $name,
  ): T {
    $spec = $specs[$name];
    invariant(
      /* HH_FIXME[4162] need reified generics */
      \is_a($spec, $class),
      'Expected %s to be a %s, got %s',
      $name,
      $class,
      \get_class($spec),
    );
    return /* HH_FIXME[4110] */ $spec;
  }

  final protected function getSimpleTyped<T>(
    classname<TypedRequestParameter<T>> $class,
    string $name,
  ): T {
    $spec = $this->getRequiredSpec($class, $name);
    $value = $this->values[$name];
    return $spec->assert($value);
  }

  final protected function getSimpleTypedOptional<T>(
    classname<TypedRequestParameter<T>> $class,
    string $name,
  ): ?T {
    $spec = $this->getOptionalSpec($class, $name);
    if (!C\contains_key($this->values, $name)) {
      return null;
    }
    $value = $this->values[$name];
    return $spec->assert($value);
  }
}
