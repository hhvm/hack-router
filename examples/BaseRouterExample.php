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

require_once(__DIR__.'/../vendor/hh_autoload.php');

use type Facebook\HackRouter\{BaseRouter, HttpMethod};

/** This can be whatever you want; in this case, it's a
 * callable, but classname<MyWebControllerBase> is also a
 * common choice.
 */
type TResponder = (function(dict<string, string>): string);

final class BaseRouterExample extends BaseRouter<TResponder> {
  <<__Override>>
  protected function getRoutes(): dict<HttpMethod, dict<string, TResponder>> {
    return dict[
      HttpMethod::GET => dict[
        '/' => ($_params) ==> 'Hello, world',
        '/user/{user_name}' => ($params) ==> 'Hello, '.$params['user_name'],
      ],
      HttpMethod::POST => dict[
        '/' => ($_params) ==> 'Hello, POST world',
      ],
    ];
  }
}

function get_example_inputs(): vec<(HttpMethod, string)> {
  return vec[
    tuple(HttpMethod::GET, '/'),
    tuple(HttpMethod::GET, '/user/foo'),
    tuple(HttpMethod::GET, '/user/bar'),
    tuple(HttpMethod::POST, '/'),
  ];
}

<<__EntryPoint>>
function main(): noreturn {
  $router = new BaseRouterExample();
  foreach (get_example_inputs() as $input) {
    list($method, $path) = $input;

    list($responder, $params) = $router->routeMethodAndPath($method, $path);
    \printf("%s %s\n\t%s\n", $method, $path, $responder($params));
  }
  exit(0);
}
