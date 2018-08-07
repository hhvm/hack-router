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

trait GetUriBuilderFromUriPattern {
  require implements HasUriPattern;

  final public static function getUriBuilder(): UriBuilder {
    return (new UriBuilder(static::getUriPattern()->getParts()));
  }
}
