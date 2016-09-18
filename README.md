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

Namespaces removed for simplicity - see
[`examples/BaseRouterExample.php`](examples/BaseRouterExample.php)) for
full executable example.
