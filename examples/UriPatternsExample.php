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

namespace Facebook\HackRouter\Examples\UrlPatternsExample;

require_once('../vendor/autoload.php');

use Facebook\HackRouter\{
  BaseRouter,
  GetFastRoutePatternFromUriPattern,
  GetUriBuilderFromUriPattern,
  HasUriPattern,
  HttpMethod,
  RequestParameters,
  UriBuilder,
  UriPattern
};

<<__ConsistentConstruct>>
abstract class WebController implements HasUriPattern {
  use GetFastRoutePatternFromUriPattern;
  use GetUriBuilderFromUriPattern;

  abstract public function getResponse(): string;

  private RequestParameters $uriParameters;
  final protected function getRequestParameters(): RequestParameters {
    return $this->uriParameters;
  }

  public function __construct(
    ImmMap<string, string> $uri_parameter_values,
  ) {
    $this->uriParameters = new RequestParameters(
      static::getUriPattern()->getParameters(),
      ImmVector { },
      $uri_parameter_values,
    );
  }
}

final class HomePageController extends WebController {
  public static function getUriPattern(): UriPattern {
    return (new UriPattern())->literal('/');
  }

  public function getResponse(): string {
    return 'Hello, world';
  }
}

final class UserPageController extends WebController {
  public static function getUriPattern(): UriPattern {
    return (new UriPattern())
      ->literal('/users/')
      ->string('user_name');
  }

  public function getResponse(): string {
    return 'Hello, '.$this->getRequestParameters()->getString('user_name');
  }
}

type TResponder = classname<WebController>;

final class UriPatternsExample extends BaseRouter<TResponder> {
  public static function getControllers(): ImmVector<TResponder> {
    return ImmVector {
      HomePageController::class,
      UserPageController::class,
    };
  }

  <<__Override>>
  public function getRoutes(
  ): ImmMap<HttpMethod, ImmMap<string, TResponder>> {
    $urls_to_controllers = Map { };
    foreach (self::getControllers() as $controller) {
      $pattern = $controller::getFastRoutePattern();
      $urls_to_controllers[$pattern] = $controller;
    }
    return ImmMap {
      HttpMethod::GET => $urls_to_controllers->immutable(),
    };
  }
}

function get_example_paths(): ImmVector<string> {
  return ImmVector {
    HomePageController::getUriBuilder()->getPath(),
    UserPageController::getUriBuilder()
      ->setString('user_name', 'Mr Hankey')
      ->getPath(),
  };
}

function main(): void {
  $router = new UriPatternsExample();
  foreach (get_example_paths() as $path) {
    list($controller, $params) = $router->routeRequest(
      HttpMethod::GET,
      $path,
    );
    printf(
      "GET %s\n\t%s\n",
      $path,
      (new $controller($params))->getResponse(),
    );
  }
}

main();
