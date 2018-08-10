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

final class RequestParametersTest extends \PHPUnit_Framework_TestCase {
  public function testStringParam(): void {
    $parts = [new StringRequestParameter(
      StringRequestParameterSlashes::WITHOUT_SLASHES,
      'foo',
    )];
    $data = dict['foo' => 'bar'];
    expect((new RequestParameters($parts, [], $data))->getString('foo'))
      ->toBeSame('bar');
  }

  public function testIntParam(): void {
    $parts = [new IntRequestParameter('foo')];
    $data = dict['foo' => '123'];
    expect((new RequestParameters($parts, [], $data))->getInt('foo'))->toBeSame(
      123,
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
    $data = dict['foo' => (string)TestIntEnum::BAR];
    $value = (new RequestParameters($parts, [], $data))->getEnum(
      TestIntEnum::class,
      'foo',
    );
    expect($value)->toBeSame(TestIntEnum::BAR);

    $typechecker_test = (TestIntEnum $x) ==> {};
    $typechecker_test($value);
  }

  public function testEnumParamToUri(): void {
    $part = (new EnumRequestParameter(TestIntEnum::class, 'foo'));
    expect($part->getUriFragment(TestIntEnum::BAR))->toBeSame(
      (string)TestIntEnum::BAR,
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
      'baz' => (string)TestIntEnum::FOO,
    ];
    $params = new RequestParameters($parts, [], $data);
    expect($params->getString('foo'))->toBeSame('some string');
    expect($params->getInt('bar'))->toBeSame(123);
    expect($params->getEnum(TestIntEnum::class, 'baz'))->toBeSame(
      TestIntEnum::FOO,
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
    expect($params->getOptionalString('foo'))->toBeSame('bar');
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
    expect($params->getOptionalString('foo'))->toBeSame(null);
  }

  /**
   * @expectedException \HH\InvariantException
   */
  public function testGetOptionalAsRequired(): void {
    $params = new RequestParameters(
      [],
      [new StringRequestParameter(
        StringRequestParameterSlashes::WITHOUT_SLASHES,
        'foo',
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
