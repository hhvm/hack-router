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

interface IResolver<+TResponder> {
  public function resolve(
    HttpMethod $method,
    string $path,
  ): (TResponder, dict<string, string>);
}
