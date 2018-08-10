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

use type Facebook\HackRouter\Tests\TestIntEnum;
use function Facebook\FBExpect\expect;
use type Facebook\HackRouter\Tests\TestStringEnum;

final class UriBuilderTest extends \PHPUnit_Framework_TestCase {
  public function testLiteral(): void {
    $parts = (new UriPattern())
      ->literal('/foo')
      ->getParts();
    expect((new UriBuilder($parts))->getPath())->toBeSame('/foo');
  }

  public function testStringParameter(): void {
    $parts = (new UriPattern())
      ->literal('/herp/')
      ->string('foo')
      ->getParts();
    $path = (new UriBuilder($parts))
      ->setString('foo', 'derp')
      ->getPath();
    expect($path)->toBeSame('/herp/derp');
  }

  public function testParameterAsFirstPart(): void {
    $parts = (new UriPattern())
      ->string('herp')
      ->getParts();
    $path = (new UriBuilder($parts))
      ->setString('herp', 'derp')
      ->getPath();
    expect($path)->toBeSame('/derp');
  }

  public function testIntParameter(): void {
    $parts = (new UriPattern())
      ->literal('/post/')
      ->int('post_id')
      ->getParts();
    $path = (new UriBuilder($parts))
      ->setInt('post_id', 123)
      ->getPath();
    expect($path)->toBeSame('/post/123');
  }

  public function testEnumParameter(): void {
    $parts = (new UriPattern())
      ->enum(TestStringEnum::class, 'foo')
      ->getParts();
    $path = (new UriBuilder($parts))
      ->setEnum(TestStringEnum::class, 'foo', TestStringEnum::BAR)
      ->getPath();
    expect($path)->toBeSame('/'.TestStringEnum::BAR);
  }

  /**
   * @expectedException \HH\InvariantException
   */
  public function testIntAsString(): void {
    $parts = (new UriPattern())->int('foo')->getParts();
    (new UriBuilder($parts))->setString('foo', 'bar');
  }

  /**
   * @expectedException \HH\InvariantException
   */
  public function testSetIncorrectEnumType(): void {
    $parts = (new UriPattern())
      ->enum(TestStringEnum::class, 'foo')
      ->getParts();
    $path = (new UriBuilder($parts))
      ->setEnum(TestIntEnum::class, 'foo', TestIntEnum::BAR);
  }

  /**
   * @expectedException \HH\InvariantException
   */
  public function testSetTwice(): void {
    $parts = (new UriPattern())->int('foo')->getParts();
    (new UriBuilder($parts))
      ->setInt('foo', 123)
      ->setInt('foo', 123);
  }

  /**
   * @expectedException \HH\InvariantException
   */
  public function testMissingValue(): void {
    $parts = (new UriPattern())->int('foo')->getParts();
    (new UriBuilder($parts))->getPath();
  }

  /**
   * @expectedException \HH\InvariantException
   */
  public function testSetInvalidParameter(): void {
    $parts = (new UriPattern())->int('foo')->getParts();
    (new UriBuilder($parts))->setInt('bar', 123);
  }
}
