Nameless Hack Micro-Framework
=============================

**WARNING** still experimental and frequently breaking API BC, pin to a
specific revision if you're crazy enough to use this.

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

A simple typed request router, built on top of `nikic/fast-route`. Example:

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
[`examples/BaseRouterExample.php`](examples/BaseRouterExample.php)) for
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

  public function getResponse(): string {
    return 'Hello, '.$this->getUriParameters()->getString('user_name');
  }
}

function main(): void {
  $uri = UserPageController::getUriBuilder()
    ->setString('user_name', 'Mr Hankey')
    ->getPath();

  $router = new UriPatternsExample();
  list($controller, $params) = $router->routeRequest(
    HttpMethod::GET,
    $uri,
  );
  $response = (new $controller($params))->getResponse();
  print($response."\n"); "// Hello, Mr Hankey"
}
```

Simplified for conciseness - see
[`examples/UriPatternsExample.php`](examples/UriPatternsExample.php)) for
full executable example.
