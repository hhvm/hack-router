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

abstract class UriBuilderBase {
  protected ImmVector<UriPatternPart> $parts;
  protected ImmMap<string, RequestParameter> $parameters;
  private Map<string, string> $values = Map {};

  public function __construct(Traversable<UriPatternPart> $parts) {
    $this->parts = new ImmVector($parts);
    $parameters = Map {};
    foreach ($parts as $part) {
      if (!$part is RequestParameter) {
        continue;
      }
      $parameters[$part->getName()] = $part;
    }
    $this->parameters = $parameters->immutable();
  }

  final protected function getPathImpl(): string {
    $uri = '';
    foreach ($this->parts as $part) {
      if ($part is UriPatternLiteral) {
        $uri .= $part->getValue();
        continue;
      }

      invariant(
        $part is RequestParameter,
        'expecting all UriPatternParts to be literals or parameters, got %s',
        \get_class($part),
      );

      if ($uri === '') {
        $uri = '/';
      }

      $name = $part->getName();
      invariant(
        $this->values->containsKey($name),
        'Parameter "%s" must be set',
        $name,
      );
      $uri .= $this->values->at($name);
    }
    invariant(
      \substr($uri, 0, 1) === '/',
      "Path '%s' does not start with '/'",
      $uri,
    );
    return $uri;
  }

  final protected function setValue<T>(
    classname<TypedUriParameter<T>> $parameter_type,
    string $name,
    T $value,
  ): this {
    $part = $this->parameters[$name] ?? null;
    invariant(
      $part !== null,
      "%s is not a valid parameter - expected one of [%s]",
      $name,
      \implode(', ', $this->parameters->keys()->map($x ==> "'".$x."'")),
    );
    invariant(
      \is_a($part, $parameter_type),
      'Expected %s to be a %s, got a %s',
      $name,
      $parameter_type,
      \get_class($part),
    );
    invariant(
      !$this->values->containsKey($name),
      'trying to set %s twice',
      $name,
    );
    /* HH_FIXME[4053] need reified generics */
    $this->values[$name] = $part->getUriFragment($value);
    return $this;
  }
}
