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

namespace Facebook\HackRouter;

use Facebook\HackRouter\Tests\TestRouter;

final class CoreTest extends \PHPUnit_Framework_TestCase {
  public function expectedMatches(
  ): array<(string, string, ImmMap<string, string>)> {
    return [
      tuple('/foo', 'root file', ImmMap {}),
      tuple('/foo/', 'root dir', ImmMap {}),
      tuple('/foo/bar', 'subfile', ImmMap {}),
      tuple('/foo/herp', 'param subfile', ImmMap { 'bar' => 'herp'}),
    ];
  }

  /**
   * @dataProvider expectedMatches
   */
  public function testMatchesPattern(
    string $in,
    string $expected_responder,
    ImmMap<string, string> $expected_data,
  ): void {
    list($actual_responder, $actual_data) =
      $this->getRouter()->routeRequest('GET', $in);
    $this->assertSame($expected_responder, $actual_responder);
    $this->assertEquals(
      $expected_data->toArray(),
      $actual_data->toArray(),
    );
  }

  /**
   * @expectedException \Facebook\HackRouter\NotFoundException
   */
  public function testNotFound(): void {
    $this->getRouter()->routeRequest('GET', '/__404');
  }

  /**
   * @expectedException \Facebook\HackRouter\MethodNotAllowedException
   */
  public function testMethodNotAllowed(): void {
    $this->getRouter()->routeRequest('POST', '/foo');
  }

  <<__Memoize>>
  private function getRouter(): TestRouter<string> {
    return new TestRouter(
      ImmMap {
        '/foo' => 'root file',
        '/foo/' => 'root dir',
        '/foo/bar' => 'subfile',
        '/foo/{bar}' => 'param subfile',
      },
    );
  }
}
