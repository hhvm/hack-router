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

use type Facebook\HackRouter\Tests\TestStringEnum;
use function Facebook\FBExpect\expect;
use type Facebook\HackRouter\Tests\TestIntEnum;
use type Facebook\HackTest\DataProvider;

final class UriPatternTest extends \Facebook\HackTest\HackTest {
  public function testLiteral(): void {
    $pattern = (new UriPattern())
      ->literal('/foo')
      ->getFastRouteFragment();
    expect($pattern)->toBeSame('/foo');
  }

  public function testMultipleLiterals(): void {
    $pattern = (new UriPattern())
      ->literal('/foo')
      ->literal('/bar')
      ->getFastRouteFragment();
    expect($pattern)->toBeSame('/foo/bar');
  }

  public function testStringParamFragment(): void {
    $pattern = (new UriPattern())
      ->literal('/~')
      ->string('username')
      ->getFastRouteFragment();
    expect($pattern)->toBeSame('/~{username}');
  }

  public function testStringWithSlashesParamFragment(): void {
    $pattern = (new UriPattern())
      ->literal('/~')
      ->stringWithSlashes('username')
      ->getFastRouteFragment();
    expect($pattern)->toBeSame('/~{username:.+}');
  }

  public function testStringParamAssertSucceeds(): void {
    expect('foo')->toBeSame(
      (
        new StringRequestParameter(
          StringRequestParameterSlashes::WITHOUT_SLASHES,
          'foo',
        )
      )->assert('foo'),
    );
  }

  public function testIntParamFragment(): void {
    $pattern = (new UriPattern())
      ->literal('/blog/')
      ->int('post_id')
      ->getFastRouteFragment();
    expect($pattern)->toBeSame('/blog/{post_id:\d+}');
  }

  public function testIntParamAssertSucceeds(): void {
    expect(
      123, // not a string
    )->toBeSame((new IntRequestParameter('foo'))->assert('123'));
  }

  public function exampleInvalidInts(): array<array<string>> {
    return [['foo'], ['0123foo'], ['0.123foo'], ['0.123'], ['0x1e3']];
  }

  <<DataProvider('exampleInvalidInts')>>
  public function testIntParamAssertThrows(string $input): void {
    expect(() ==> {
      (new IntRequestParameter('foo'))->assert($input);
    })->toThrow(InvariantException::class);
  }

  public function testStringEnumParamFragment(): void {
    $pattern = (new UriPattern())
      ->literal('/foo/')
      ->enum(TestStringEnum::class, 'my_param')
      ->getFastRouteFragment();
    expect($pattern)->toBeSame('/foo/{my_param:(?:herp|derp)}');
  }

  public function testStringEnumParamAssertSucceeds(): void {
    expect(
      (new EnumRequestParameter(TestStringEnum::class, 'param_name'))
        ->assert((string)TestStringEnum::FOO),
    )->toBeSame(TestStringEnum::FOO);
  }

  public function testIntEnumParamAssertSucceeds(): void {
    expect(
      (new EnumRequestParameter(TestIntEnum::class, 'param_name'))
        ->assert((string)TestIntEnum::FOO),
    )->toBeSame(TestIntEnum::FOO);
  }

  public function testEnumParamAssertFails(): void {
    expect(() ==> {
      (new EnumRequestParameter(TestStringEnum::class, 'param_name'))
        ->assert('not a valid enum value');
    })->toThrow(\UnexpectedValueException::class);
  }
}
