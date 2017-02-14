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

trait GetFastRoutePatternFromUriPattern{
  require implements HasUriPattern;

  final public static function getFastRoutePattern(): string {
    return static::getUriPattern()->getFastRouteFragment();
  }
}
