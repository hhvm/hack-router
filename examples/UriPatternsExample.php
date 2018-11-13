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

require_once(__DIR__.'/../vendor/hh_autoload.php');

use   type Facebook\HackRouter\{
  BaseRouter,
  GetFastRoutePatternFromUriPattern,
  GetUriBuilderFromUriPattern,
  HasUriPattern,
  HttpMethod,
  RequestParameters,
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
  <<__Override>>
  public static function getUriPattern(): UriPattern {
    return (new UriPattern())->literal('/');
  }

  <<__Override>>
  public function getResponse(): string {
    return 'Hello, world';
  }
}

final class UserPageController extends WebController {
  <<__Override>>
  public static function getUriPattern(): UriPattern {
    return (new UriPattern())
      ->literal('/users/')
      ->string('user_name');
  }

  <<__Override>>
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
    $urls_to_controllers = dict[];
    foreach (self::getControllers() as $controller) {
      $pattern = $controller::getFastRoutePattern();
      $urls_to_controllers[$pattern] = $controller;
    }
    return ImmMap {
      HttpMethod::GET => new ImmMap($urls_to_controllers),
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
    list($controller, $params) = $router->routeMethodAndPath(
      HttpMethod::GET,
      $path,
    );
    \printf(
      "GET %s\n\t%s\n",
      $path,
      (new $controller($params))->getResponse(),
    );
  }
}

/* HH_IGNORE_ERROR[1002] top-level statement in strict file */
main();
