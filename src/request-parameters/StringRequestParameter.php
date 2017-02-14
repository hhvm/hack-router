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

enum StringRequestParameterSlashes : string {
  ALLOW_SLASHES = 'allow';
  WITHOUT_SLASHES = 'no_slashes';
}

final class StringRequestParameter
extends TypedUriParameter<string> {
  public function __construct(
    private StringRequestParameterSlashes $slashes,
    string $name,
  ) {
    parent::__construct($name);
  }

  <<__Override>>
  public function assert(string $input): string{
    if ($this->slashes === StringRequestParameterSlashes::WITHOUT_SLASHES) {
      invariant(
        strpos($input, '/') === false,
        'Parameter %s contains slashes',
        $this->getName(),
      );
    }
    return $input;
  }

  <<__Override>>
  public function getRegExpFragment(): ?string {
    if ($this->slashes === StringRequestParameterSlashes::WITHOUT_SLASHES) {
      return null;
    }
    return '.+';
  }
}
