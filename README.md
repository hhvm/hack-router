Nameless Hack Micro-Framework
=============================

**WARNING** still experimental and frequently breaking API BC, pin to a
specific revision if you're crazy enough to use this.

Components
==========

Router
------

A simple, typed, request router, built on top of nikic/fast-route. Example:

```Hack
<?hh // strict

class Router extends \Facebook\HackRouter\BaseRouter<
  classname<BaseController>,
  classname<ReadController>,
  classname<WriteController:>
> {
  protected function getGETRoutes(): ImmMap<string, ReadController> {
    return ImmMap {
      '/foo/bar' => FooController::class,
    };
  }

  protected function getPOSTRoutes(): ImmMap<string, WriteController> {
    return ImmMap {
      '/herp/derp' => BarController::class,
    };
  }
}

function main() {
  list($controller, $params) = (new Router())->routeRequest('GET', '/foo/bar');
  // $controller is guaranteed to be a classname<BaseController>
}
