# eArc-router

This is the router component of the [earc framework](https://github.com/Koudela/eArc-core). 
It also can be used within other frameworks or as standalone component.

The earc router does not use any predefined routes - they are expressed via the 
filesystem which is transformed into an observer tree.

Given this direct mapping between the url and the directory structure, understanding 
the apps routing process is as simple as typing `tree` at the base of the routing 
directory.

The two immutable objects Route and Request are attached to the event the router 
dispatches on the observer tree. Route decomposes an url path in parameters matching 
the maximal path in the routing dir/observer tree (real arguments) and a the rest 
(virtual arguments). Request supplies the information about the http request variables. 

## Install

```
$ composer require earc/router
```

## Bootstrap 

earc/router uses [earc/di](https://github.com/Koudela/eArc-di) for dependency
injection. 

```php
use eArc\DI\DI;

require_once '/path/to/your/vendor/dir/autoload.php';

DI::init();
```

Place the above code in the section where your script/framework is 
bootstrapped.

## Configure

earc/router uses [earc/event-tree](https://github.com/Koudela/eArc-eventTree) to
pass routing events to observers represented by the directory structure. You
need an folder within your namespace that is the root for the event tree.

```php
di_import_param(['earc' => [
    'vendor_directory' => __DIR__.'/../../vendor',
    'event_tree' => [
        'directories' => [
            '../path/to/your/eventTree/root/folder' => '\\your\\eventTree\\root\\namespace',
        ]   
    ]
]]);
```

The path to the root folder has to be relative to your projects vendor directory.

Of course you can use a yml file to define the configuration array.
 
```php
di_import_param(Yaml::parse(file_get_contents('/path/to/config.yml')));
```

## Use

Since we use the native tree data structures of the modern operating systems to
organize our code it is a tiny step to define our routes and targeting controller.

It is as easy as it can get.
 
1. Go to the event tree root directory and make a new subdirectory `routing`.
2. For every route make subdirectories for the fixed part and put a class at
the end extending the `AbstractController`.
3. Use the `process()` method and the passed `RouterEvent` to hook your controller
logic.

### The controller




### Pre and post processing router events via listeners attached to the route

### Pre and post processing router events via live cycle hooks



Each controller action (as you may know from other frameworks) 
is a listener of its own. In most use cases they listen to the event phase `EventRouter::PHASE_DESTINATION`. 
Please note that the routing event tree shares its base directory with other event 
trees.  

Each middleware that hooks into the apps livecycle can be registered via an
listener of its own. Middleware listens to the event phase 
`EventRouter::PHASE_ACCESS` mainly.

Once you have written the action and middleware listeners, you can build and dispatch
the event.

The router event is always build with an url, request method and variables. Thus 
each router event instance is always bound to a valid request and can be easily 
serialized and saved for later use if necessary. The request variables can be set 
to `null` to initialize the auto import of the 'INPUT_*' variables.

```php
# bootstrap.php

use eArc/router/RouterEvent;

$event = new RouterEvent(
    $_REQUEST['url'] ?? '/',
    $_SERVER['REQUEST_METHOD'],
    $requestVariables
);

$event->dispatch();
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
whatever you like, but it has to implement the Controller Interface.

```php
# /absolute/path/to/your/project/src/tree/routing/admin/somestuff/edit/MyFooListener.php
 
namespace namespace\of\src\tree\routing\admin\somestuff\edit;

use eArc\eventTree\Interfaces\PhaseSpecificListenerInterface
use eArc\eventTree\Interfaces\ObserverTreeInterface
use eArc\Router\Interfaces\ControllerInterface

class MyFooListener implements ControllerInterface, PhaseSpecificListenerInterface
{
    public function processEvent(Event $event)
    {
        //... your controller code goes here
        
        // $param_0 === '2342'
        $param_0 = $event->getRouteInformation('route')->getVirtualArgs(0);
        
        // you can use earc/di to get hold of your dependencies
        $service = di_get(Service::class);
        
        // retrieve the POST request immutable
        $request = $event->getRequestInformation('POST');
        
        // calling some third party stuff from the container
        di_get(FactoryService::class)->getTwig()->render('index.html', array()); 
    }
    
    public static function getPhase()
    {
        return ObserverTreeInterface::PHASE_DESTINATION;
    }
}
```  

If you use the route somewhere you can use the `earc_route` function with the
fully qualified controller class name as argument to retrieve it.

## The access listeners

Because of the `EARC_LISTENER_TYPE` `EventRouter::PHASE_ACCESS` the following
event listener is always called when the event get past `admin` (e.g. the url 
starts with `admin/`).

```php
# /absolute/path/to/your/project/src/tree/routing/admin/Bouncer.php
 
namespace namespace\of\src\tree\routing\admin;

use eArc\eventTree\Interfaces\PhaseSpecificListenerInterface
use eArc\eventTree\Interfaces\PhaseSpecificListenerInterface
use eArc\eventTree\Interfaces\ObserverTreeInterface
use eArc\Router\Interfaces\ControllerInterface

class Bouncer implements ControllerInterface, PhaseSpecificListenerInterface, SortableListenerInterface  
{

    public function processEvent(Event $event)
    {
        if (... user is not logged in ...) {

            ... serialize and save $event ...
            ... you can use this to dispatch a similar event later ... 

            $event->getHandler->kill();
            
            (new RouterEvent('/login', 'GET', []))->dispatch();
            
            return;            
        } else if (... user has not the admin privileges...) {
            $event->kill();

            (new RouterEvent('/login/access-denied', 'GET', []))->dispatch();
        }
    }
    
    public static function getPatience()
    {
        return -100;
    }
    
    public static funciton getPhase()
    {
        EventRouter::PHASE_ACCESS;
    }
}
```  
  
You might have realized by now that checking the access rights of your users and
rerouting them is as easy as drinking a cup of tea. It will be real hard to mess 
it up.

## The route immutable

The `Route` object is immutable and exposes the six methods of the `RouteInformationInterface` 
for retrieving route information. It can be accessed via `getRouteInformation()`.

```php
$route = $event->getRouteInformation();


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
It can be accessed via `getRequestInformation($requestType)`.

```php
$request = $event->getRequestInformation($requestType);


$request->getRequestType(); // string ('GET', 'POST', 'PUT', 'PATCH', 'DELETE', ...) 

$request->hasRequestArg(string $name); // bool

$request->getRequestArg(string $name); // mixed

$request->getRequestArgs(); // array
```

## Further reading 

- Since the eArc/router is build on top of the [earc/event-tree](https://github.com/Koudela/eArc-eventTree) 
please feel free to consult the earc/event-tree documentation.

- To deepen the understanding of the power of this routing concept reading the 
chapter about the access controllers in the  [earc/core manual](https://github.com/Koudela/eArc-core#the-access-controllers)
might be a good idea. 

## Releases

### release v1.0

- The route is now matched against an 
[earc/event-tree](https://github.com/Koudela/eArc-eventTree) instead of a 
directory tree.
- The dispatcher is now part of earc/router instead of earc/core and dispatches 
an earc/event-tree event.
- Introduces the immutable objects `Route` and `Request`. Both are attached as 
payload to the dispatched earc/event-tree event.
- There are no controllers anymore. Access- and main-controllers are now
represented as earc/event-tree listeners.

### release v0.1

This is the first official release.


TODO:
- composer
- Tests 
- ParameterInterface
- Expose Live-Circle via eventTree 
