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

use type Facebook\HackRouter\Tests\{TestIntEnum, TestStringEnum};
use function Facebook\FBExpect\expect;

final class RequestParametersTest extends \Facebook\HackTest\HackTest {
  public function testStringParam(): void {
    $parts = varray[new StringRequestParameter(
      StringRequestParameterSlashes::WITHOUT_SLASHES,
      'foo',
    )];
    $data = dict['foo' => 'bar'];
    expect((new RequestParameters($parts, varray[], $data))->getString('foo'))
      ->toBeSame('bar');
  }

  public function testIntParam(): void {
    $parts = varray[new IntRequestParameter('foo')];
    $data = dict['foo' => '123'];
    expect((new RequestParameters($parts, varray[], $data))->getInt('foo'))->toBeSame(
      123,
    );
  }

  public function testFetchingStringAsInt(): void {
    expect(() ==> {
      $parts = varray[new StringRequestParameter(
        StringRequestParameterSlashes::WITHOUT_SLASHES,
        'foo',
      )];
      $data = dict['foo' => 'bar'];
      (new RequestParameters($parts, varray[], $data))->getInt('foo');
    })->toThrow(InvariantException::class);
  }

  public function testEnumParam(): void {
    $parts = varray[new EnumRequestParameter(TestIntEnum::class, 'foo')];
    $data = dict['foo' => (string)TestIntEnum::BAR];
    $value = (new RequestParameters($parts, varray[], $data))->getEnum(
      TestIntEnum::class,
      'foo',
    );
    expect($value)->toBeSame(TestIntEnum::BAR);

    $typechecker_test = (TestIntEnum $_x) ==> {};
    $typechecker_test($value);
  }

  public function testEnumParamToUri(): void {
    $part = (new EnumRequestParameter(TestIntEnum::class, 'foo'));
    expect($part->getUriFragment(TestIntEnum::BAR))->toBeSame(
      (string)TestIntEnum::BAR,
    );
  }

  public function testInvalidEnumParamToUri(): void {
    expect(() ==> {
      $part = (new EnumRequestParameter(TestIntEnum::class, 'foo'));
      /* HH_IGNORE_ERROR[4110] intentionally bad type */
      $_throws = $part->getUriFragment(TestStringEnum::BAR);
    })->toThrow(\UnexpectedValueException::class);
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
    $params = new RequestParameters($parts, varray[], $data);
    expect($params->getString('foo'))->toBeSame('some string');
    expect($params->getInt('bar'))->toBeSame(123);
    expect($params->getEnum(TestIntEnum::class, 'baz'))->toBeSame(
      TestIntEnum::FOO,
    );
  }

  public function testGetOptional(): void {
    $params = new RequestParameters(
      varray[],
      varray[new StringRequestParameter(
        StringRequestParameterSlashes::WITHOUT_SLASHES,
        'foo',
      )],
      dict['foo' => 'bar'],
    );
    expect($params->getOptionalString('foo'))->toBeSame('bar');
  }

  public function testGetMissingOptional(): void {
    $params = new RequestParameters(
      varray[],
      varray[new StringRequestParameter(
        StringRequestParameterSlashes::WITHOUT_SLASHES,
        'foo',
      )],
      dict[],
    );
    expect($params->getOptionalString('foo'))->toBeSame(null);
  }

  public function testGetOptionalAsRequired(): void {
    expect(() ==> {
      $params = new RequestParameters(
        varray[],
        varray[new StringRequestParameter(
          StringRequestParameterSlashes::WITHOUT_SLASHES,
          'foo',
        )],
        dict['foo' => 'bar'],
      );
      $params->getString('foo');
    })->toThrow(InvariantException::class);
  }

  public function testGetRequiredAsOptional(): void {
    expect(() ==> {
      $params = new RequestParameters(
        varray[new StringRequestParameter(
          StringRequestParameterSlashes::WITHOUT_SLASHES,
          'foo',
        )],
        varray[],
        dict['foo' => 'bar'],
      );
      $params->getOptionalString('foo');
    })->toThrow(InvariantException::class);
  }
}
