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

Once you have written the action and middleware listeners, you can build the 
[observer tree](https://github.com/Koudela/eArc-eventTree/blob/master/doc/tree.md).
Just use the `ObserverTreeFactory` of the event tree package. 

```php
use eArc\EventTree\Transformation\ObserverTreeFactory;

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

## The action listeners

Lets look at the URI `https://your-domain.de/admin/somestuff/edit/2342`.
In frameworks like Symfony your route would be `admin/somestuff/` probably 
calling the method `editAction` from the `SomeStuffController` class. Supplying
`2342` as parameter. The eArc router handles it slightly different, but not 
much:

In your routing directory there is the `admin` subdirectory and therein the 
`somestuff` directory with the `edit` directory. Obviously there will be no 
`2342` directory. In the `edit` directory lives a listener. You can name it
whatever you like.

```php
namespace your\event\tree\root\namespace\routing\admin\somestuff\edit;

use eArc\EventTree\Event\Event;
use eArc\EventTree\Interfaces\EventListener;

class MyFooListener implements EventListener
{
    const EARC_LISTENER_PATIENCE = 1;
    const EARC_LISTENER_TYPE = EventRouter::PHASE_DESTINATION;

    public function processEvent(Event $event)
    {
        //... your controller code goes here
        
        // $param0 === '2342'
        $param0 = $event->getPayload('route')->getVirtualArgs(0);
        
        // if the root event has a container attached
        $container = $event->getContainer();
        
        // retrieve the request immutable
        $request = $event->getPayload('request');
        
        // calling some third party stuff from the container
        $conainer->get('twig')->render('index.html', array()); 
    }
}
```  

## The access listeners

Because of the `EARC_LISTENER_TYPE` `EventRouter::PHASE_ACCESS` the following
event listener is always called when the event get past `admin` (e.g. the url 
starts with `admin/`).

```php
namespace your\event\tree\root\namespace\routing\admin;

use eArc\EventTree\Event\Event;
use eArc\EventTree\Interfaces\EventListener;

use eArc\Router\Api\Dispatcher;

class Bouncer implements EventListener
{
    const EARC_LISTENER_PATIENCE = -100;
    const EARC_LISTENER_TYPE = EventRouter::PHASE_ACCESS;

    public function processEvent(Event $event)
    {
        if (... user is not locked in ...) {

            ... serialize and save $event->getPayload('route') ...
            ... serialize and save $event->getPayload('request') ...
            ... you can use this to dispatch a similar event later ...

            $event->silence();
            $event->kill();
            
            new Dispatcher($event->getTree(), $event)
                ->dispatch('/login', [], 'GET');
            
            return;            
        } else if (... user has not the admin privileges...) {
            $event->silence();
            $event->kill();

            new Dispatcher($event->getTree(), $event)
                ->dispatch('/login/access-denied', [], 'GET');
        }
    }
}
```  
  
You might have realized by now that checking the access rights of your users and
rerouting them is as easy as drinking a cup of tea. It will be real hard to mess 
it up.

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
$request = $event->getPayload('request');


$request->getRequestType(); // string ('GET', 'POST', 'PUT', 'PATCH', 'DELETE', ...) 

$request->hasRequestArg(string $name); // bool

$request->getRequestArg(string $name); // mixed

$request->getRequestArgs(); // array
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
