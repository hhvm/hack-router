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

abstract class UriParametersBase {
  private ImmMap<string, RequestParameter> $specs;

  public function __construct(
    Traversable<RequestParameter> $spec_vector,
    protected ImmMap<string, string> $values,
  ) {
    $specs = Map { };
    foreach ($spec_vector as $spec) {
      $specs[$spec->getName()] = $spec;
    }
    $this->specs = $specs->immutable();
  }

  final protected function getSpec<T as RequestParameter>(
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
    classname<TypedRequestParameter<T>> $class,
    string $name,
  ): T {
    $spec = $this->getSpec($class, $name);
    $value = $this->values->at($name);
    return $spec->assert($value);
  }
}
