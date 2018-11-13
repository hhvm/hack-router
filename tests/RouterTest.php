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
use namespace HH\Lib\Dict;
use type Facebook\HackRouter\Tests\TestRouter;
use type Facebook\HackTest\DataProvider;
use type Usox\HackTTP\{ServerRequestFactory, UriFactory};
use type Facebook\Experimental\Http\Message\HTTPMethod;

final class RouterTest extends \Facebook\HackTest\HackTest {
  const keyset<string>
    MAP = keyset[
      '/foo',
      '/foo/',
      '/foo/bar',
      '/foo/bar/{baz}',
      '/foo/{bar}',
      '/foo/{bar}/baz',
      '/foo/{bar}{baz:.+}',
      '/food/{noms}',
      '/bar/{herp:\\d+}',
      '/bar/{herp}',
      '/unique/{foo}/bar',
      '/optional_suffix_[foo]',
      '/optional_suffix[/]',
      '/optional_suffixes/[herp[/derp]]',
      '/manual/en/{LegacyID}.php',
    ];

  public function expectedMatches(
  ): array<(string, string, dict<string, string>)> {
    return [
      tuple('/foo', '/foo', dict[]),
      tuple('/foo/', '/foo/', dict[]),
      tuple('/foo/bar', '/foo/bar', dict[]),
      tuple('/foo/bar/herp', '/foo/bar/{baz}', dict['baz' => 'herp']),
      tuple('/foo/herp', '/foo/{bar}', dict['bar' => 'herp']),
      tuple('/foo/=%3Efoo', '/foo/{bar}', dict['bar' => '=>foo']),
      tuple('/foo/herp/baz', '/foo/{bar}/baz', dict['bar' => 'herp']),
      tuple(
        '/foo/herp/derp',
        '/foo/{bar}{baz:.+}',
        dict['bar' => 'herp', 'baz' => '/derp'],
      ),
      tuple('/food/burger', '/food/{noms}', dict['noms' => 'burger']),
      tuple('/bar/123', '/bar/{herp:\\d+}', dict['herp' => '123']),
      tuple('/bar/derp', '/bar/{herp}', dict['herp' => 'derp']),
      tuple('/bar/1derp', '/bar/{herp}', dict['herp' => '1derp']),
      tuple('/unique/foo/bar', '/unique/{foo}/bar', dict['foo' => 'foo']),
      tuple('/optional_suffix_', '/optional_suffix_[foo]', dict[]),
      tuple('/optional_suffix_foo', '/optional_suffix_[foo]', dict[]),
      tuple('/optional_suffix', '/optional_suffix[/]', dict[]),
      tuple('/optional_suffix/', '/optional_suffix[/]', dict[]),
      tuple('/optional_suffixes/', '/optional_suffixes/[herp[/derp]]', dict[]),
      tuple(
        '/optional_suffixes/herp',
        '/optional_suffixes/[herp[/derp]]',
        dict[],
      ),
      tuple(
        '/optional_suffixes/herp/derp',
        '/optional_suffixes/[herp[/derp]]',
        dict[],
      ),
      tuple(
        '/manual/en/foo.php',
        '/manual/en/{LegacyID}.php',
        dict['LegacyID' => 'foo'],
      ),
      tuple(
        '/manual/en/foo.bar.php',
        '/manual/en/{LegacyID}.php',
        dict['LegacyID' => 'foo.bar'],
      ),
    ];
  }

  public function testCanGetExpatchedMatchesWithResolvers(): void {
    $_ = $this->expectedMatchesWithResolvers();
  }

  public function getAllResolvers(
  ): array<
    (
      string,
      (function(dict<HttpMethod, dict<string, string>>): IResolver<string>),
    )
  > {
    return [
      tuple('simple regexp', $map ==> new SimpleRegexpResolver($map)),
      tuple(
        'prefix matching',
        $map ==> PrefixMatchingResolver::fromFlatMap($map),
      ),
    ];
  }

  public function expectedMatchesWithResolvers(
  ): array<(string, IResolver<string>, string, string, dict<string, string>)> {
    $map = dict[HttpMethod::GET => dict(self::MAP)];
    $resolvers = Dict\from_entries($this->getAllResolvers());

    $out = [];
    $examples = $this->expectedMatches();
    foreach ($resolvers as $name => $resolver) {
      $resolver = $resolver($map);
      foreach ($examples as $ex) {
        $out[] = tuple($name, $resolver, $ex[0], $ex[1], $ex[2]);
      }
    }
    return $out;
  }

  <<DataProvider('getAllResolvers')>>
  public function testMethodNotAllowedResponses(
    string $_name,
    (function(dict<HttpMethod, dict<string, string>>): IResolver<string>)
      $factory,
  ): void {
    $map = dict[
      HttpMethod::GET => dict[
        'getonly' => 'getonly',
      ],
      HttpMethod::HEAD => dict[
        'headonly' => 'headonly',
      ],
      HttpMethod::POST => dict[
        'postonly' => 'postonly',
      ],
    ];

    $router = $this->getRouter()->setResolver($factory($map));

    list($responder, $_data) =
      $router->routeMethodAndPath(HttpMethod::HEAD, 'getonly');
    expect($responder)->toBeSame('getonly');
    expect(() ==> $router->routeMethodAndPath(HttpMethod::GET, 'headonly'))->toThrow(
      MethodNotAllowedException::class,
    );
    expect(() ==> $router->routeMethodAndPath(HttpMethod::HEAD, 'postonly'))->toThrow(
      MethodNotAllowedException::class,
    );
    expect(() ==> $router->routeMethodAndPath(HttpMethod::GET, 'postonly'))->toThrow(
      MethodNotAllowedException::class,
    );
  }

  <<DataProvider('expectedMatches')>>
  public function testMatchesPattern(
    string $in,
    string $expected_responder,
    dict<string, string> $expected_data,
  ): void {
    list($actual_responder, $actual_data) =
      $this->getRouter()->routeMethodAndPath(HttpMethod::GET, $in);
    expect($actual_responder)->toBeSame($expected_responder);
    expect(dict($actual_data))->toBeSame($expected_data);
  }

  <<DataProvider('expectedMatchesWithResolvers')>>
  public function testAllResolvers(
    string $_resolver_name,
    IResolver<string> $resolver,
    string $in,
    string $expected_responder,
    dict<string, string> $expected_data,
  ): void {
    list($responder, $data) = $this->getRouter()
      ->setResolver($resolver)
      ->routeMethodAndPath(HttpMethod::GET, $in);
    expect($responder)->toBeSame($expected_responder);
    expect(dict($data))->toBeSame($expected_data);

    list($responder, $data) = $resolver->resolve(HttpMethod::GET, $in);
    expect($responder)->toBeSame($expected_responder);
    expect($data)->toBeSame(dict($data));

    list($responder, $data) = $this->getRouter()
      ->setResolver($resolver)
      ->routeMethodAndPath(HttpMethod::HEAD, $in);
    expect($responder)->toBeSame($expected_responder);
    expect(dict($data))->toBeSame($expected_data);
  }

  <<DataProvider('expectedMatches')>>
  public function testRequestResponseInterfacesSupport(
    string $path,
    string $_expected_responder,
    dict<string, string> $_expected_data,
  ): void {
    $router = $this->getRouter();
    list($direct_responder, $direct_data) =
      $router->routeMethodAndPath(HttpMethod::GET, $path);

    expect($path[0])->toBeSame('/');

    $psr_request = (new ServerRequestFactory())->createServerRequest(
      HTTPMethod::GET,
      (new UriFactory())->createUri('http://example.com'.$path),
      /* server params = */ dict[],
    );
    list($psr_responder, $psr_data) = $router->routeRequest($psr_request);
    expect($psr_responder)->toBeSame($direct_responder);
    expect($psr_data)->toBePHPEqual($direct_data);
  }

  <<DataProvider('getAllResolvers')>>
  public function testNotFound(
    string $_resolver_name,
    (function(dict<HttpMethod, dict<string, string>>): IResolver<string>)
      $factory,
  ): void {
    $router = $this->getRouter()->setResolver($factory(dict[]));
    expect(() ==> $router->routeMethodAndPath(HttpMethod::GET, '/__404'))->toThrow(
      NotFoundException::class,
    );

    $router = $this->getRouter()
      ->setResolver($factory(dict[
        HttpMethod::GET => dict['/foo' => '/foo'],
      ]));
    expect(() ==> $router->routeMethodAndPath(HttpMethod::GET, '/__404'))->toThrow(
      NotFoundException::class,
    );
  }

  public function testMethodNotAllowed(): void {
    expect(() ==> {
      $this->getRouter()->routeMethodAndPath(HttpMethod::POST, '/foo');
    })->toThrow(\Facebook\HackRouter\MethodNotAllowedException::class);
  }

  public function testCovariantTResponder(): void {
    $router = $this->getRouter();
    $this->typecheckCovariantTResponder($router, $router);
  }

  private function typecheckCovariantTResponder(
    BaseRouter<arraykey> $_,
    BaseRouter<string> $_,
  ): void {}


  private function getRouter(): TestRouter<string> {
    return new TestRouter(dict(self::MAP));
  }
}
