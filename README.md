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

## Table of contents

 - [Install](#install)
 - [Bootstrap](#bootstrap)
 - [Configure](#configure)
 - [Use](#use)
    - [The controller](#the-controller)
    - [The router event](#the-router-event)
 - [Advanced usage](#advanced-usage)
    - [Pre and post processing](#pre-and-post-processing)
        - [Via listeners attached to the route](#via-listeners-attached-to-the-route)
        - [Via live cycle hooks](#via-live-cycle-hooks)
    - [Customized events](#customized-events)
    - [Customized routes](#customized-routes)

## Install

```
$ composer require earc/router
```

## Bootstrap 

Place the following code snippets in the section where your script/framework is 
bootstrapped.

1. Make use of the composer namespace driven autoloading.

```php
require_once '/path/to/your/vendor/dir/autoload.php';
```
                                                   
2. Then bootstrap [earc/di](https://github.com/Koudela/eArc-di) for dependency injection. 

```php
use eArc\DI\DI;

DI::init();
```

3. Configure the router (see [configure](#configure)).

4. And dispatch the router event to call the responsible controller(s).

```php
use eArc\Router\RouterEvent;

$event = new RouterEvent();
$event->dispatch();
```

## Configure

earc/router uses [earc/event-tree](https://github.com/Koudela/eArc-eventTree) to
pass routing events to observers represented by the directory structure. You
need an folder within your namespace that is the root for the event tree.

```php
di_import_param(['earc' => [
    'vendor_directory' => '/path/to/your/vendor/dir',
    'event_tree' => [
        'directories' => [
            'earc/router/earc-event-tree' => 'eArc\\RouterEventTreeRoot',
            '../path/to/your/eventTree/root/folder' => '\\your\\eventTree\\root\\namespace',
        ]   
    ]
]]);
```

The path to the root folder has to be relative to your projects vendor directory.

Of course you can use a yml file to define the configuration array.
 
```php
di_import_param(Yaml::parse(file_get_contents('/path/to/your/config.yml')));
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

Given you plan to use the urls `/admin/user`, `/admin/user/edit/{id}`, `/admin/user/add` 
and `/admin/user/delete` for their obvious purpose. Then you have two options:

1. Either you place one controller in the `routing/admin/user` directory (with **no** 
subdirectories named `edit`, `add` or `delte`). Then all user managing logic has
to be spawned in this one controller.

2. Or you place one controller in the `routing/admin/user` directory and a second in 
the `routing/admin/user/edit` directory and another in the `routing/admin/user/add`
directory and the last in the `routing/admin/user/delete` directory. 

The second is the recommended way. Since the routing mechanism does not support
parametrized method calling and it forces the programmers to move business logic
out of the controller. Nevertheless if you want to stick to the first way you can 
implement it in an abstract `BaseController` extending the `AbstractController`. 
You need only a few lines of code.

Your controller have to extend the `AbstractController`.

```php
namespace NamespaceOfThe\EventTreeRoot\routing\admin\user\edit;

use eArc\Router\AbstractController;
use eArc\Observer\Interfaces\EventInterface;
use eArc\Router\RouterEvent;

class Controller extends AbstractController
{
    public function process(EventInterface $event) : void
    {
        //... your controller code goes here

        // if you need the routing Parameters, you should check if it is an Routing Event.
        if ($event instanceof RouterEvent) {
            //... a very basic example without form processing

            // the parameters are the route arguments that does not match a directory        
            $id = $event->getRoute()->getParam(1);
            // if you use doctrine the next step could look like this
            $user = di_get(UserRepository::class)->find($id);
            // calling some third party rendering engine    
            di_get(EngineInterface::class)->render('templates/user/edit.html', ['user' => $user]);
        }
    }
}
```

Since your controllers have all different Namespaces you can name them all
controller. But it is recommended to name them in a more explicit way.
 
### The router event

Every router event carries information about the request and the route. They are
saved in a request immutable (access via `$event->getRequest()`) and a route 
immutable (access via `$event->getRoute()`). For details consult the `RouteInformationInterface`
and the `RequestInformationInterface`.

## Advanced usage

### Pre and post processing 

There are a tons of examples where logic needs to be executed before or after
the controller logic. They can be divided into three cases.
1. The logic is specific to one route/controller.
2. The logic is specific to a sub route/class of controllers.
3. The logic is useful for all or nearly all controllers.

#### Via listeners attached to the route

The first two cases can use the fact, that the earc/router uses the 
[earc/event-tree](https://github.com/Koudela/eArc-eventTree) package. The route
events travel from the routing folder to the targeted controller and can be
intercepted via listeners. 

Lets start with the second case first.

The router event triggers all listeners that implement the `RouterListenerInterface`.
Suppose you want to check the admin privileges for all routes starting with `/admin`.
Simply put a class in the `routing/admin` folder that implements the 
`RouterListenerInterface` and process the event analogue to the controller. You can
even kill the event if it shall not reach any controller. Of course you can spawn
a new one that routes to `/login` or `/error-pages/access-denied`.

```php
namespace NamespaceOfThe\EventTreeRoot\routing\admin\user\edit;

use eArc\Router\Interfaces\RouterListenerInterface;
use eArc\Router\RouterEvent;
use eArc\Observer\Interfaces\EventInterface;

class Listener implements RouterListenerInterface
{
    public function process(EventInterface $event) : void
    {
        //... your listener code goes here

        if ($event instanceof RouterEvent) {
            //... 
            // the user is not logged in
            $event->getHandler()->kill();
            (new RouterEvent('/login'))->dispatch();
            // ...
            // the user has no admin privileges
            $event->getHandler()->kill();
            (new RouterEvent('/error-pages/access-denied'))->dispatch();
            //...
        }
    }
}
```

Please note a new router event does not redirect the browser client. It simply
changes the flow of the apps request processing. To make an redirect change the 
path from `/login` to `/redirect/login` and place into the `routing/redirect` folder
an listener or controller that makes an redirect.

As you can see you can even use the router event to decouple your logic. Something
all event trees have in common.

Let us look at the second case now.

You can put a listener in the same directory a the controller. It has to implement
the `SortableListenerInterface` in order to get called before or after the controller.
And if it shall be called only if the route is targeting the controller the listener 
it has to implement the `PhaseSpecificListenerInterface` too. 

```php
use eArc\EventTree\Transformation\ObserverTree;
use eArc\EventTree\Interfaces\PhaseSpecificListenerInterface;
use eArc\Router\Interfaces\RouterListenerInterface;
use eArc\Observer\Interfaces\EventInterface;
use eArc\EventTree\Interfaces\SortableListenerInterface;

class Listener implements RouterListenerInterface, SortableListenerInterface, PhaseSpecificListenerInterface
{
    public function process(EventInterface $event) : void
    {
        //...
    }
                          
    public static function getPatience() : float
    {
        // it has a negative patience and is hence called before the controller
        // who has a patience of 0.
        return -1;
    }

    public static function getPhase(): int
    {
        // listener with phase destination are only called if the route matches
        return ObserverTree::PHASE_DESTINATION;
    }
}
```

#### Via live cycle hooks



### Customized events



### Customized routes

Internationalization etc. 

The router event is always build with an url, request method and variables. Thus 
each router event instance is always bound to a valid request and can be easily 
serialized and saved for later use if necessary. The request variables can be set 
to `null` to initialize the auto import of the 'INPUT_*' variables.

```php
use eArc\Router\RouterEvent;

$event = new RouterEvent(
    $_REQUEST['url'] ?? '/',
    $_SERVER['REQUEST_METHOD'],
    $requestVariables
);

$event->dispatch();
```



Each controller action (as you may know from other frameworks) 
is a listener of its own. In most use cases they listen to the event phase `EventRouter::PHASE_DESTINATION`. 
Please note that the routing event tree shares its base directory with other event 
trees.  

Each middleware that hooks into the apps livecycle can be registered via an
listener of its own. Middleware listens to the event phase 
`EventRouter::PHASE_ACCESS` mainly.

Once you have written the action and middleware listeners, you can build and dispatch
the event.


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
use eArc\eventTree\Interfaces\PhaseSpecificListenerInterface;
use eArc\Router\Interfaces\RouterListenerInterface;

class MyFooListener implements 
```  

If you use the route somewhere you can use the `earc_route` function with the
fully qualified controller class name as argument to retrieve it.

  
You might have realized by now that checking the access rights of your users and
rerouting them is as easy as drinking a cup of tea. It will be real hard to mess 
it up.

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
- The router live cycle is exposed via an event tree. Making it easy to implement
pre and post processing.

### release v0.1

This is the first official release.


TODO:
- composer
- Tests 
- ParameterInterface
