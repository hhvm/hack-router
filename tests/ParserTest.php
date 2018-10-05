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

final class ParserTest extends \Facebook\HackTest\HackTest {
  public function getExamplePatterns(): array<(string, string)> {
    return [
      tuple('/foo', "['/foo']"),
      tuple('/foo/{bar}', "['/foo/', {bar}]"),
      tuple('/foo/[{bar}]', "['/foo/', ?[{bar}]]"),
      tuple("/foo/{bar:\\d+}", "['/foo/', {bar: #\\d+#}]"),
      tuple('/foo/{bar:[0-9]+}', "['/foo/', {bar: #[0-9]+#}]"),
      tuple('/foo/{bar:[0-9]{1,3}}', "['/foo/', {bar: #[0-9]{1,3}#}]"),
    ];
  }

  <<DataProvider('getExamplePatterns')>>
  public function testPattern(string $pattern, string $expected): void {
    expect(PatternParser\Parser::parse($pattern)->toStringForDebug())
      ->toBeSame($expected);
  }
}
