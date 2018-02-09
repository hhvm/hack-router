<?hh
/*
 *  Copyright (c) 2015-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the BSD-style license found in the
 *  LICENSE file in the root directory of this source tree. An additional grant
 *  of patent rights can be found in the PATENTS file in the same directory.
 *
 */

/***********
 * IF YOU EDIT THIS FILE also update the snippet in README.md
 ***********/

namespace Facebook\HackRouter\Examples\BaseRouterExample;

require_once(__DIR__.'/../vendor/hh_autoload.php');

use Facebook\HackRouter\BaseRouter;
use Facebook\HackRouter\HttpMethod;

/** This can be whatever you want; in this case, it's a
 * callable, but classname<MyWebControllerBase> is also a
 * common choice.
 */
type TResponder = (function(dict<string, string>):string);

final class BaseRouterExample extends BaseRouter<TResponder> {
  <<__Override>>
  protected function getRoutes(
  ): ImmMap<HttpMethod, ImmMap<string, TResponder>> {
    return ImmMap {
      HttpMethod::GET => ImmMap {
        '/' =>
          ($_params) ==> 'Hello, world',
        '/user/{user_name}' =>
          ($params) ==> 'Hello, '.$params['user_name'],
      },
      HttpMethod::POST => ImmMap {
        '/' => ($_params) ==> 'Hello, POST world',
      },
    };
  }
}

function get_example_inputs(): ImmVector<(HttpMethod, string)> {
  return ImmVector {
    tuple(HttpMethod::GET, '/'),
    tuple(HttpMethod::GET, '/user/foo'),
    tuple(HttpMethod::GET, '/user/bar'),
    tuple(HttpMethod::POST, '/'),
  };
}

function main(): void {
  $router = new BaseRouterExample();
  foreach (get_example_inputs() as $input) {
    list($method, $path) = $input;

    list($responder, $params) = $router->routeRequest($method, $path);
    \printf(
      "%s %s\n\t%s\n",
      $method,
      $path,
      $responder(dict($params)),
    );
  }
}

main();
