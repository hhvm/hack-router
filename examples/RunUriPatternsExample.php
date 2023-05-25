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

namespace Facebook\HackRouter\Examples\UrlPatternsExample;

use type Facebook\HackRouter\HttpMethod;

<<__EntryPoint>>
function main(): void {
  require_once __DIR__.'/../vendor/autoload.hack';
  \Facebook\AutoloadMap\initialize();

  $router = new UriPatternsExample();
  foreach (get_example_paths() as $path) {
    list($controller, $params) =
      $router->routeMethodAndPath(HttpMethod::GET, $path);
    \printf("GET %s\n\t%s\n", $path, (new $controller($params))->getResponse());
  }
}
