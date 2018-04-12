Hack-Router [![Build Status](https://travis-ci.org/hhvm/hack-router.svg?branch=master)](https://travis-ci.org/hhvm/hack-router)
===========

Fast, type-safe request routing, parameter retrieval, and link generation, with PSR-7 support.

Components
==========

HTTP Exceptions
---------------

Exception classes representing common situations in HTTP applications:
 - `InternalServerError`
 - `MethodNotAllowed`
 - `NotFoundException`

BaseRouter
----------

A simple typed request router. Example:

```Hack
<?hh // strict
/** TResponder can be whatever you want; in this case, it's a
 * callable, but classname<MyWebControllerBase> is also a
 * common choice.
 */
type TResponder = (function(ImmMap<string, string>):string);

final class BaseRouterExample extends BaseRouter<TResponder> {
  protected function getRoutes(
  ): ImmMap<HttpMethod, ImmMap<string, TResponder>> {
    return ImmMap {
      HttpMethod::GET => ImmMap {
        '/' =>
          ($_params) ==> 'Hello, world',
        '/user/{user_name}' =>
          ($params) ==> 'Hello, '.$params['user_name'],
      },
      HttpMethod::POST => ImmMap {
        '/' => ($_params) ==> 'Hello, POST world',
      },
    };
  }
}
```

Simplified for conciseness - see
[`examples/BaseRouterExample.php`](examples/BaseRouterExample.php) for
full executable example.

UriPatterns
-----------

Generate FastRoute fragments, URIs (for linking), and retrieve URI parameters
in a consistent and type-safe way:

```Hack
<?hh // strict
final class UserPageController extends WebController {
  public static function getUriPattern(): UriPattern {
    return (new UriPattern())
      ->literal('/users/')
      ->string('user_name');
  }
  // ...
}
```

Parameters can be retrevied, with types checked at runtime both against the
values, and the definition:

```Hack
public function getResponse(): string {
  return 'Hello, '.$this->getUriParameters()->getString('user_name');
}
```

You can also generate links to controllers:

```Hack
$link = UserPageController::getUriBuilder()
  ->setString('user_name', 'Mr Hankey')
  ->getPath();
```

These examples are simplified for conciseness - see
[`examples/UriPatternsExample.php`](examples/UriPatternsExample.php) for
full executable example.

Codegen
-------

The [hhvm/hack-router-codegen](https://github.com/hhvm/hack-router-codegen)
project builds on top of of this project to automatically generate:

 - Full request routing objects and URI maps based on UriPatterns defined in the
   controllers
 - Per-controller parameter classes, allowing `$params->getFoo()` instead of
   `$params->getString('Foo')`; this allows the typechecker to catch more
   errors, and IDE autocomplete functionality to support parameters.
 - Per-controller UriBuilder classes, with similar benefits

Contributing
============

We welcome GitHub issues and pull requests - please see CONTRIBUTING.md for details.

License
=======

hack-router is MIT-licensed.
