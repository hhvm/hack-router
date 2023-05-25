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

use function Facebook\FBExpect\expect;
use type Facebook\HackRouter\Tests\NoHeadGetRewriteRouter;
use type Facebook\HackTest\DataProvider;

final class RouterHeadToGetDisabledTest extends \Facebook\HackTest\HackTest {
  public function getAllResolvers(): vec<(
    string,
    (function(dict<HttpMethod, dict<string, string>>): IResolver<string>),
  )> {
    return vec[
      tuple('simple regexp', $map ==> new SimpleRegexpResolver($map)),
      tuple('prefix matching', PrefixMatchingResolver::fromFlatMap<>),
    ];
  }

  <<DataProvider('getAllResolvers')>>
  public function testMethodNotAllowedResponses(
    string $_name,
    (function(
      dict<HttpMethod, dict<string, string>>,
    ): IResolver<string>) $factory,
  ): void {
    $map = dict[
      HttpMethod::GET => dict['/get' => 'get'],
      HttpMethod::HEAD => dict['/head' => 'head'],
    ];

    $router = $this->getRouter()->setResolver($factory($map));

    // GET -> HEAD ( no re-routing ), deviating from the default behavior.
    $e = expect(() ==> $router->routeMethodAndPath(HttpMethod::HEAD, '/get'))
      ->toThrow(MethodNotAllowedException::class);
    expect($e->getAllowedMethods())->toBeSame(keyset[HttpMethod::GET]);

    // GET -> HEAD
    $e = expect(() ==> $router->routeMethodAndPath(HttpMethod::GET, '/head'))
      ->toThrow(MethodNotAllowedException::class);
    expect($e->getAllowedMethods())->toBeSame(keyset[HttpMethod::HEAD]);
  }

  private function getRouter(): NoHeadGetRewriteRouter<string> {
    return new NoHeadGetRewriteRouter(dict[]);
  }
}
