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
use type Facebook\HackRouter\Tests\TestStringEnum;

final class UriBuilderTest extends \PHPUnit_Framework_TestCase {
  public function testLiteral(): void {
    $parts = (new UriPattern())
      ->literal('/foo')
      ->getParts();
    $this->assertSame(
      '/foo',
      (new UriBuilder($parts))->getPath(),
    );
  }

  public function testStringParameter(): void {
    $parts = (new UriPattern())
      ->literal('/herp/')
      ->string('foo')
      ->getParts();
    $path = (new UriBuilder($parts))
      ->setString('foo', 'derp')
      ->getPath();
    $this->assertSame(
      '/herp/derp',
      $path,
    );
  }

  public function testParameterAsFirstPart(): void {
    $parts = (new UriPattern())
      ->string('herp')
      ->getParts();
    $path = (new UriBuilder($parts))
      ->setString('herp', 'derp')
      ->getPath();
    $this->assertSame(
      '/derp',
      $path,
    );
  }

  public function testIntParameter(): void {
    $parts = (new UriPattern())
      ->literal('/post/')
      ->int('post_id')
      ->getParts();
    $path = (new UriBuilder($parts))
      ->setInt('post_id', 123)
      ->getPath();
    $this->assertSame(
      '/post/123',
      $path,
    );
  }

  public function testEnumParameter(): void {
    $parts = (new UriPattern())
      ->enum(TestStringEnum::class, 'foo')
      ->getParts();
    $path = (new UriBuilder($parts))
      ->setEnum(TestStringEnum::class, 'foo', TestStringEnum::BAR)
      ->getPath();
    $this->assertSame(
      '/'.TestStringEnum::BAR,
      $path,
    );
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
