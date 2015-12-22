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

namespace Facebook\HackRouter\ClassnameTypecheckTest;

use Facebook\HackRouter\BaseRouter;

abstract class BaseController {}
abstract class ReadController extends BaseController {}
abstract class WriteController extends BaseController {}

abstract class ClassnameTypecheckTest extends BaseRouter<
  classname<BaseController>,
  classname<ReadController>,
  classname<WriteController>
> {}
