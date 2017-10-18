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

use \Facebook\HackRouter\Tests\TestIntEnum;
use \Facebook\HackRouter\Tests\TestStringEnum;

final class RequestParametersTest extends \PHPUnit_Framework_TestCase {
  public function testStringParam(): void {
    $parts = [new StringRequestParameter(
      StringRequestParameterSlashes::WITHOUT_SLASHES,
      'foo',
    )];
    $data = dict['foo' => 'bar'];
    $this->assertSame(
      'bar',
      (new RequestParameters($parts, [], $data))->getString('foo'),
    );
  }

  public function testIntParam(): void {
    $parts = [new IntRequestParameter('foo')];
    $data = dict['foo' => '123'];
    $this->assertSame(
      123,
      (new RequestParameters($parts, [], $data))->getInt('foo'),
    );
  }

  /**
   * @expectedException \HH\InvariantException
   */
  public function testFetchingStringAsInt(): void {
    $parts = [new StringRequestParameter(
      StringRequestParameterSlashes::WITHOUT_SLASHES,
      'foo',
    )];
    $data = dict['foo' => 'bar'];
    (new RequestParameters($parts, [], $data))->getInt('foo');
  }

  public function testEnumParam(): void {
    $parts = [new EnumRequestParameter(TestIntEnum::class, 'foo')];
    $data = dict['foo' => (string) TestIntEnum::BAR];
    $value = (new RequestParameters($parts, [], $data))->getEnum(
      TestIntEnum::class,
      'foo',
    );
    $this->assertSame(
      TestIntEnum::BAR,
      $value,
    );

    $typechecker_test = (TestIntEnum $x) ==> {};
    $typechecker_test($value);
  }

  public function testEnumParamToUri(): void {
    $part = (new EnumRequestParameter(TestIntEnum::class, 'foo'));
    $this->assertSame(
      (string) TestIntEnum::BAR,
      $part->getUriFragment(TestIntEnum::BAR),
    );
  }

  /**
   * @expectedException UnexpectedValueException
   */
  public function testInvalidEnumParamToUri(): void {
    $part = (new EnumRequestParameter(TestIntEnum::class, 'foo'));
    /* HH_IGNORE_ERROR[4110] intentionally doing the wrong thing */
    $_throws = $part->getUriFragment(TestStringEnum::BAR);
  }

  public function testFromPattern(): void {
    $parts = (new UriPattern())
      ->literal('/')
      ->string('foo')
      ->literal('/')
      ->int('bar')
      ->literal('/')
      ->enum(TestIntEnum::class, 'baz')
      ->getParameters();
    $data = dict[
      'foo' => 'some string',
      'bar' => '123',
      'baz' => (string) TestIntEnum::FOO,
    ];
    $params = new RequestParameters($parts, [], $data);
    $this->assertSame(
      'some string',
      $params->getString('foo'),
    );
    $this->assertSame(
      123,
      $params->getInt('bar'),
    );
    $this->assertSame(
      TestIntEnum::FOO,
      $params->getEnum(TestIntEnum::class, 'baz'),
    );
  }

  public function testGetOptional(): void {
    $params = new RequestParameters(
      [],
      [new StringRequestParameter(
        StringRequestParameterSlashes::WITHOUT_SLASHES,
        'foo',
      )],
      dict['foo' => 'bar'],
    );
    $this->assertSame(
      'bar',
      $params->getOptionalString('foo'),
    );
  }

  public function testGetMissingOptional(): void {
    $params = new RequestParameters(
      [],
      [new StringRequestParameter(
        StringRequestParameterSlashes::WITHOUT_SLASHES,
        'foo',
      )],
      dict[],
    );
    $this->assertSame(
      null,
      $params->getOptionalString('foo'),
    );
  }

  /**
   * @expectedException \HH\InvariantException
   */
  public function testGetOptionalAsRequired(): void {
    $params = new RequestParameters(
      [],
      [new StringRequestParameter(
        StringRequestParameterSlashes::WITHOUT_SLASHES,
        'foo'
      )],
      dict['foo' => 'bar'],
    );
    $params->getString('foo');
  }

  /**
   * @expectedException \HH\InvariantException
   */
  public function testGetRequiredAsOptional(): void {
    $params = new RequestParameters(
      [new StringRequestParameter(
        StringRequestParameterSlashes::WITHOUT_SLASHES,
        'foo',
      )],
      [],
      dict['foo' => 'bar'],
    );
    $params->getOptionalString('foo');
  }
}
