<?hh // strict
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

use \Facebook\HackRouter\Tests\TestRouter;
use \Zend\Diactoros\ServerRequest;
use function Facebook\FBExpect\expect;
use namespace HH\Lib\{C, Str};

final class RouterTest extends \PHPUnit_Framework_TestCase {
  const dict<string, string> MAP = dict[
    '/foo' => 'root file',
    '/foo/' => 'root dir',
    '/foo/bar' => 'subfile',
    '/foo/bar/{baz}' => 'prefix param subfile',
    '/foo/{bar}' => 'param subfile',
    '/foo/{bar}/baz' => 'subfile under param',
    '/foo/{bar}{baz:.+}' => 'subparam',
  ];

  public function expectedMatches(
  ): array<(string, string, dict<string, string>)> {
    return [
      tuple('/foo', 'root file', dict[]),
      tuple('/foo/', 'root dir', dict[]),
      tuple('/foo/bar', 'subfile', dict[]),
      tuple('/foo/bar/herp', 'prefix param subfile', dict['baz' => 'herp']),
      tuple('/foo/herp', 'param subfile', dict['bar' => 'herp']),
      tuple('/foo/herp/baz', 'subfile under param', dict['bar' => 'herp']),
      tuple('/foo/herp/derp', 'subparam', dict['bar' => 'herp', 'baz' => '/derp']),
    ];
  }

  public function testCanGetExpatchedMatchesWithResolvers(): void {
    $_ = $this->expectedMatchesWithResolvers();
  }

  public function expectedMatchesWithResolvers(
  ): array<(string, IResolver<string>, string, string, dict<string, string>)> {
    $map = dict[HttpMethod::GET => self::MAP];
    $resolvers = dict[
      'fastroute' => new FastRouteResolver($map, null),
      'simple regexp' => new SimpleRegexpResolver($map),
      'prefix matching' => new PrefixMatchingResolver($map),
    ];

    $out = [];
    $examples = $this->expectedMatches();
    foreach ($resolvers as $name => $resolver) {
      foreach ($examples as $ex) {
        $out[] = tuple($name, $resolver, $ex[0], $ex[1], $ex[2]);
      }
    }
    return $out;
  }

  /**
   * @dataProvider expectedMatches
   */
  public function testMatchesPattern(
    string $in,
    string $expected_responder,
    dict<string, string> $expected_data,
  ): void {
    list($actual_responder, $actual_data) =
      $this->getRouter()->routeRequest(HttpMethod::GET, $in);
    expect($actual_responder)->toBeSame($expected_responder);
    expect($actual_data)->toBeSame($expected_data);
  }

  /**
   * @dataProvider expectedMatchesWithResolvers
   */
  public function testAllResolvers(
    string $resolver_name,
    IResolver<string> $resolver,
    string $in,
    string $expected_responder,
    dict<string, string> $expected_data,
  ): void {
    list($responder, $data) = $resolver->resolve(HttpMethod::GET, $in);
    expect($responder)->toBeSame($expected_responder);
    expect($data)->toBeSame($expected_data);
  }

  /**
   * @dataProvider expectedMatches
   */
  public function testPsr7Support(
    string $path,
    string $_expected_responder,
    dict<string, string> $_expected_data,
  ): void {
    $router = $this->getRouter();
    list($direct_responder, $direct_data) = $router->routeRequest(
      HttpMethod::GET,
      $path,
    );

    /* HH_FIXME[2049] no HHI for Diactoros */
    $psr_request = new ServerRequest(
      /* server = */ [],
      /* file = */ [],
      'http://example.com/'.$path,
      'GET',
      /* body = */ '/dev/null',
      /* headers = */ [],
    );
    list($psr_responder, $psr_data) = $router->routePsr7Request($psr_request);
    $this->assertSame(
      $direct_responder,
      $psr_responder,
    );
    $this->assertEquals(
      $direct_data,
      $psr_data,
    );
  }

  /**
   * @expectedException \Facebook\HackRouter\NotFoundException
   */
  public function testNotFound(): void {
    $this->getRouter()->routeRequest(HttpMethod::GET, '/__404');
  }

  /**
   * @expectedException \Facebook\HackRouter\MethodNotAllowedException
   */
  public function testMethodNotAllowed(): void {
    $this->getRouter()->routeRequest(HttpMethod::POST, '/foo');
  }

  public function testCovariantTResponder(): void {
    $router = $this->getRouter();
    $this->_testCovariantTResponder($router, $router);
  }

  public function _testCovariantTResponder(BaseRouter<arraykey> $_, BaseRouter<string> $_): void {}


  private function getRouter(
  ): TestRouter<string> {
    return new TestRouter(self::MAP);
  }
}
