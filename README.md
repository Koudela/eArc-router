# eArc router

This is the router component of the 
[eArc framework](https://github.com/Koudela/eArc-core). It also can be used
within other frameworks or as standalone component.

The eArc router does not use any predefined routes - they are expressed via the
filesystem which is transformed into an 
[observer tree](https://github.com/Koudela/eArc-eventTree/blob/master/doc/tree.md).

Given this direct mapping between the url and the directory structure,
understanding the apps routing process is as simple as typing `tree` at the base
of the routing directory.

The two immutable objects Route and Request are attached as payload to the
[event](https://github.com/Koudela/eArc-eventTree/blob/master/doc/event.md)
the router dispatches on the observer tree. Route decomposes an url path in 
parameters matching the maximal path in the routing dir/observer dir (real 
arguments) and a the rest (virtual arguments). Request supplies the information
about the http request. 

## Installation

```
$ composer install earc/router
```

Hint: If you want to install the eArc framework as new project use the
[earc/minimal package](https://github.com/Koudela/eArc-minimal).

## Basic Usage

As always you can use the composer autoloader.

```php
include 'path/to/your/project/dir/' . 'vendor/autoload.php';
```

First of all you need a directory where your eArc/eventTree 
[listeners](https://github.com/Koudela/eArc-eventTree/blob/master/doc/listener.md)
live in. Each controller action (as you may know from Symfony) is a listener of 
its own. In most use cases they listen to the event phase 
`EventRouter::PHASE_DESTINATION`. Please note that the routing event tree shares 
its base directory with other event trees.  

```php
$eventTreeDir = '/path/to/event/tree/base/dir'
$routingDir = $eventTreeDir . '/routing'
```

Each middleware that hooks into the apps livecycle can be registered via an
listener of its own. Middleware listens to the event phase 
`EventRouter::PHASE_ACCESS` mainly. 

Once we have the action and middleware listeners written, we can build the 
[observer tree](https://github.com/Koudela/eArc-eventTree/blob/master/doc/tree.md).
Just use the `ObserverTreeFactory` of the event tree package. 

```php
use eArc\eventTree\Transformation\ObserverTreeFactory;

$OTF = new ObserverTreeFactory(
    $eventTreeDir, 
    'your\\event\\tree\\root\\namespace'
);
```

Now your code knows where your routing trees live. You can build the tree via
the `get` method.

```php
$routingTree = $OTF->get('routing');
```

The `toString` method might be helpful for debugging purposes.

```php
echo $routingTree->toString();
```

Using a root event might be a good idea if you want to supply a di-container or 
some general payload to the routing/request event. In the other cases you can 
just set it to `null`.

```php
use eArc\Router\Api\Dispatcher;

    '/absolute/path/to/your/routing/base/dir',

$dispatcher = new Dispatcher(
    $routingTree,
    $rootEvent
);
```

The routing/request event is always dispatched with an url, request variables 
and method. Thus each routing/request event instance is always bound to a valid 
request and can be easily serialized and saved for later use if necessary. The
request variables can be set to `null` to initialize the auto import of the 
'INPUT_*' variables.
 
```php
$dispatcher->dispatch(
    $_REQUEST['url'] ?? '/',
    $requestVariables,
    $_SERVER['REQUEST_METHOD']
 );
```

## The action listener

TODO: ...

## The access listener

TODO: ...

## The route immutable

The `Route` object is immutable and exposes the six methods of the 
`RouteInformationInterface` for retrieving route
information. It can be accessed via the event payload key `'route'`.

```php
$route = $event->getPayload('route');


$route->cntRealArgs(); // int (number real parameter) 

$route->getRealArg(int $pos); // ?string (real parameter at positon $pos)

$route->getRealArgs(); // array (all real parameter; key === position)


$route->cntVirtualArgs(); // int (number virtual parameter)

$route->getVirtualArg(int $pos); // ?string (virtual parameter at position $pos)

$route->getVirtualArgs(): // array (all virtual parameter; key === position)
```

## The request immutable

The `Request` object is immutable and exposes the four methods of the 
`RequestInformationInterface` for retrieving information concerning the request. 
It can be accessed via the event payload key `'request'`.

```php
$router->getRequestType(); // string ('GET', 'POST', 'PUT', 'PATCH', 'DELETE', ...) 

```

TODO: ...

## Example (OLD V0.1)

Given some directories and files:

```
/path/to/routing/base/dir/
/path/to/routing/base/dir/somefile.php

/path/to/routing/base/dir/office/
/path/to/routing/base/dir/office/access.php
/path/to/routing/base/dir/office/main.php
/path/to/routing/base/dir/office/anotherfile.html

/path/to/routing/base/dir/office/admin/
/path/to/routing/base/dir/office/admin/access.php
/path/to/routing/base/dir/office/admin/main.php

/path/to/routing/base/dir/office/admin/user/
/path/to/routing/base/dir/office/admin/user/access.php
/path/to/routing/base/dir/office/admin/user/here_is_no_main.php
```

And an URL:

```
http://example.com/office/admin/user/200/expanded
```

The real arguments are:

```php
[
    0 => '',
    1 => 'office', 
    2 => 'admin'
]
```

The virtual arguments are:

```php
[
    0 => 'user',
    1 => '200',
    2 => 'expanded'
]
```

The absolute paths to the access controllers are:


```php
[
    0 => '/path/to/routing/base/dir/office/access.php',
    1 => '/path/to/routing/base/dir/office/admin/access.php'
]
```

You might have realized that checking the access rights of your users is now as
easy as drinking a cup of tea. It will be real hard to mess it up.
 
Obviously the file `/path/to/routing/base/dir/office/admin/user/access.php` does 
not belong to the access controllers as `user` is a virtual argument. The
argument is virtual since the absolute path to the main controller is:

```php
'/path/to/routing/base/dir/office/admin/main.php'
```

  

## Further reading 

- To deepen the understanding of the power of this routing concept reading the 
chapter about the access controllers in the 
[eArc core manual](https://github.com/Koudela/eArc-core#the-access-controllers)
might be a good idea. 

- Since the eArc/router is build on top of the
[eArc/eventTree](https://github.com/Koudela/eArc-eventTree) please feel free to 
consult the eArc/eventTree  
[documentation](https://github.com/Koudela/eArc-eventTree/blob/master/README.md).

## Releases

### release v1.0

- The route is now matched against an 
[eArc/eventTree](https://github.com/Koudela/eArc-eventTree) instead of a 
directory tree.
- The dispatcher is now part of eArc/router instead of eArc/core and dispatches 
an eArc/eventTree event.
- Introduces the immutable objects Route and Request. Both are attached as 
payload to the dispatched eArc/eventTree event.
- There are no controllers anymore. Access- and main-controllers are now
represented as eArc/eventTree listeners.

### release v0.1

This is the first official release.
