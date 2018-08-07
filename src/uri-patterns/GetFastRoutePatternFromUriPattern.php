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

trait GetFastRoutePatternFromUriPattern {
  require implements HasUriPattern;

  final public static function getFastRoutePattern(): string {
    return static::getUriPattern()->getFastRouteFragment();
  }
}
