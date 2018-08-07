<?hh // strict
/*
 *  Copyright (c) 2015-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace Facebook\HackRouter\PrefixMatching;


final class PrefixMapOrResponder<T> {
  public function __construct(
    private ?PrefixMap<T> $map,
    private ?T $responder,
  ) {
    invariant(
      ($map === null) !== ($responder === null),
      'Must specify map *or* responder',
    );
  }

  public function isMap(): bool {
    return $this->map !== null;
  }

  public function isResponder(): bool {
    return $this->responder !== null;
  }

  public function getMap(): PrefixMap<T> {
    $map = $this->map;
    invariant($map !== null, 'Called getMap() when !isMap()');
    return $map;
  }

  public function getResponder(): T {
    $responder = $this->responder;
    invariant($responder !== null, 'Called getResponder() when !isResponder');
    return $responder;
  }

  public function getSerializable(): mixed where T as string {
    if ($this->isMap()) {
      return $this->getMap()->getSerializable();
    }
    return $this->responder;
  }
}
