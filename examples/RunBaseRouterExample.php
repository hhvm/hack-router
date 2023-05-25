<?hh // strict
/*
 *  Copyright (c) 2015-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

/***********
 * IF YOU EDIT THIS FILE also update the snippet in README.md
 ***********/

namespace Facebook\HackRouter\Examples\BaseRouterExample;

<<__EntryPoint>>
function main(): noreturn {
  require_once __DIR__.'/../vendor/autoload.hack';
  \Facebook\AutoloadMap\initialize();

  $router = new BaseRouterExample();
  foreach (get_example_inputs() as $input) {
    list($method, $path) = $input;

    list($responder, $params) = $router->routeMethodAndPath($method, $path);
    \printf("%s %s\n\t%s\n", $method, $path, $responder(dict($params)));
  }
  exit(0);
}
