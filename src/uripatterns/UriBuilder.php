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

class UriBuilder {
  private ImmVector<UriPatternPart> $parts;
  private ImmMap<string, UriPatternParameter> $parameters;
  private Map<string, string> $values = Map { };

  final public function __construct(
    Traversable<UriPatternPart> $parts,
  ) {
    $this->parts = new ImmVector($parts);
    $parameters = Map { };
    foreach ($parts as $part) {
      if (!$part instanceof UriPatternParameter) {
        continue;
      }
      $parameters[$part->getName()] = $part;
    }
    $this->parameters = $parameters->immutable();
  }

  final public function getPath(): string {
    $uri = '';
    foreach ($this->parts as $part) {
      if ($part instanceof UriPatternLiteral) {
        $uri .= $part->getValue();
        continue;
      }

      invariant(
        $part instanceof UriPatternParameter,
        'expecting all UriPatternParts to be literals or parameters, got %s',
        get_class($part),
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
      substr($uri, 0, 1) === '/',
      "Path '%s' does not start with '/'",
      $uri,
    );
    return $uri;
  }

  final public function setValue<T>(
    classname<UriPatternTypedParameter<T>> $parameter_type,
    string $name,
    T $value,
  ): this {
    $part = $this->parameters[$name] ?? null;
    invariant(
      $part !== null,
      "%s is not a valid parameter - expected one of [%s]",
      $name,
      implode(
        ', ',
        $this->parameters->keys()->map($x ==> "'".$x."'"),
      ),
    );
    invariant(
      $part instanceof $parameter_type,
      'Expected %s to be a %s, got a %s',
      $name,
      $parameter_type,
      get_class($part),
    );
    invariant(
      !$this->values->containsKey($name),
      'trying to set %s twice',
      $name,
    );
    $this->values[$name] = $part->getUriFragment($value);
    return $this;
  }

  ///// Convenience Accessors /////

  final public function setString(string $name, string $value): this {
    return $this->setValue(
      UriPatternStringParameter::class,
      $name,
      $value,
    );
  }

  final public function setInt(string $name, int $value): this {
    return $this->setValue(
      UriPatternIntParameter::class,
      $name,
      $value,
    );
  }

  final public function setEnum<T>(
    /* HH_FIXME[2053] */ classname<\HH\BuiltinEnum<T>> $class,
    string $name,
    T $value,
  ): this {
    $spec = $this->parameters[$name] ?? null;
    if ($spec && $spec instanceof UriPatternEnumParameter) {
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
      UriPatternEnumParameter::class,
      $name,
      $class::assert($value),
    );
  }
}
