# earc Router

This is the router component of the [earc framework](https://github.com/Koudela/eArc-core). 
It also can be used within other frameworks or as standalone component.

The earc router does not use any configured routes - they are expressed via the 
filesystem which is transformed into an observer tree.

Given this direct mapping between the url and the directory structure, understanding 
the apps routing process is as simple as typing `tree` at the base of the routing 
directory.

## Table of contents

 - [Install](#install)
 - [Bootstrap](#bootstrap)
 - [Configure](#configure)
 - [Basic usage](#basic-usage)
    - [The controller](#the-controller)
    - [The response controller](#the-response-controller)
    - [The router event](#the-router-event)
    - [Routes with special characters](#routes-with-special-characters)
 - [Advanced usage](#advanced-usage)
    - [Pre and post processing](#pre-and-post-processing)
        - [Via listeners attached to the route](#via-listeners-attached-to-the-route)
        - [Via live cycle hooks](#via-live-cycle-hooks)
    - [Customized events](#customized-events)
    - [Routing/event tree inheritance](#routingevent-tree-inheritance)
    - [Subsystem handling](#subsystem-handling)
    - [Further decoupling](#further-decoupling)
    - [Customized routes](#customized-routes)
        - [Rewriting of routes](#rewriting-of-routes)
          - [The redirect directive](#the-redirect-directive)
          - [The lookup directive](#the-lookup-directive)
          - [The routing directory](#the-routing-directory)
        -  [Mapping routes](#mapping-routes)
    - [Serializing events](#serializing-events)
    - [Caching the routing tree](#caching-the-routing-tree)
 - [Further reading](#further-reading)
 - [Releases](#releases)
    - [Release 3.1](#release-31)
    - [Release 3.0](#release-30)
    - [Release 2.1](#release-21)
    - [Release 2.0](#release-20)
    - [Release 1.1](#release-11)
    - [Release 1.0](#release-10)
    - [Release 0.1](#release-01) 
 
## Install

```
$ composer require earc/router
```

## Bootstrap 

Place the following code snippets in the section where your script/framework is 
bootstrapped.

1 . Make use of the composer namespace driven autoloading.

```php
require_once '/path/to/your/vendor/autoload.php';
```
                                                   
2 . Then bootstrap [earc/di](https://github.com/Koudela/eArc-di) for dependency 
injection and [earc/core](https://github.com/Koudela/eArc-core) for the configuration
file.
 
```php
use eArc\Core\Configuration;
use eArc\DI\DI;

DI::init();
Configuration::build();
```

3 . Configure the router (see [configure](#configure)).

4 . And dispatch the router event to call the responsible controller(s).

```php
use eArc\Router\RouterEvent;

$event = new RouterEvent();
$event->dispatch();
```

## Configure

earc/router uses [earc/event-tree](https://github.com/Koudela/eArc-eventTree) to
pass routing events to observers represented by the directory structure. You
need a folder within your namespace that is the root for the event tree.

Put the parameters in a file named `.earc-config.php` beneath 
the vendor folder.

```php
<?php #.earc-config.php

return ['earc' => [
    'is_production_environment' => false,
    'event_tree' => [
        'directories' => [
            'earc/router/earc-event-tree' => 'eArc\\RouterEventTreeRoot',
            // your configuration part:
            '../path/to/your/eventTree/root/folder' => 'NamespaceOfYour\\EventTreeRoot',
        ]   
    ]
]];

```

The path to the event tree root folder has to be absolute or relative to your projects 
vendor directory.

Of course you can use a YAML file to define the configuration array.
 
```php
<?php #.earc-config.php

return Yaml::parse(file_get_contents('/path/to/your/config.yml'));

```

## Basic usage

Since we use the native tree data structures of the modern operating systems to
organize our code it is a tiny step to define our routes and targeting controller.

It is as easy as it can get.
 
1. Go to the event tree root directory and make a new subdirectory `routing`.
2. For every route make subdirectories for the fixed part and put a class at
the end extending the `eArc\Router\AbstractController`.
3. Use the `process()` method and the passed `RouterEvent` to hook in your controller
logic.

### The controller

Given you plan to use the urls `/admin/user`, `/admin/user/edit/{id}`, `/admin/user/add` 
and `/admin/user/delete` for their obvious purpose, you have two options:

1. Either you place one controller in the `routing/admin/user` directory (with **no** 
subdirectories named `edit`, `add` or `delete`). Then all user managing logic has
to be spawned in this one controller.

2. Or you place one controller in the `routing/admin/user` directory and a second in 
the `routing/admin/user/edit` directory and another in the `routing/admin/user/add`
directory and the last in the `routing/admin/user/delete` directory. 

The second is the recommended way.

A controller would look like this:

```php
namespace NamespaceOfYour\EventTreeRoot\routing\admin\user\edit;

use eArc\Router\AbstractController;
use eArc\Router\Interfaces\RouterEventInterface;

class Controller extends AbstractController
{
    public function process(RouterEventInterface $event) : void
    {
        //... your controller code goes here

        //... a very basic example without form processing:

        // the parameters are the route arguments that does not match a directory        
        $id = $event->getRoute()->getParam(0);
        // if you use doctrine the next step could look like this
        $user = di_get(UserRepository::class)->find($id);
        // calling some third party rendering engine    
        di_get(EngineInterface::class)->render('templates/user/edit.html', ['user' => $user]);
    }
}
```

One action in every controller is supreme to the traditional way of parametrized 
method calling:

1. It forces programmers to move business logic out of the controller.
2. Every action is exposed to pre and post processing. You can add 
the logic without touching anything but the filesystem. (See pre and post processing 
[via listeners attached to the route](#via-listeners-attached-to-the-route) for
details.)

Nevertheless if you want to stick to the traditional way you may implement the 
logic in an abstract `BaseController` extending the  `AbstractController`. Or use 
the [live cycle hooks](#via-live-cycle-hooks) of the earc router (recommended). For 
the parametrized method calling logic itself you will need three lines of code at 
most.

Since your controllers all have different namespaces you can name them all
`Controller`. But it is recommended to name them in a more explicit way.
 
### The response controller

Whereas the `AbstractController` is a traditional 
[earc/event-tree](https://github.com/Koudela/eArc-eventTree) listener, the 
`AbstractResponseController` is a step away from events towards the requirements 
of routing. It is available since release 2.1 and supports parameter injection 
and transformation. 

This type of controller would look like this:

```php
namespace NamespaceOfYour\EventTreeRoot\routing\admin\user\edit;

use eArc\Router\AbstractResponseController;
use eArc\Router\Interfaces\ResponseInterface;
use eArc\Router\Response;

class Controller extends AbstractResponseController
{
    public function respond(?User $user) : ResponseInterface
    {
        //... your controller code goes here

        //... a very basic example without form processing:

        // calling some third party rendering engine    
        return new Response(di_get(EngineInterface::class)->render('templates/user/edit.html', ['user' => $user]));
    }
}
```

Hint: Nullable types transform the string parameter `'null'` to the `null` value.
That makes it possible to send a null value as part of an url. 

The controller code looks a bit cleaner and saves you a view lines. It does not 
come for free though. The earc router does know the build in primitive types of php, 
the entities defined via [earc/data](https://github.com/Koudela/eArc-data) and
the interfaces it is shipped with (`RouterEventInterface`,`RouteInformationInterface`
and the `RequestInformationInterface`).

Hint: Union types are not supported by the transformer and will throw a
`TypeHintException`.

You have to extend the existing logic to support other type hints. This can be done
in two separate ways. (The example uses the well known doctrine orm.)

1. Implement the `ParameterFactoryInterface`. 

 ```php
    use eArc\Router\Interfaces\ParameterFactoryInterface;
    
    class MyEntity implements ParameterFactoryInterface
    {
        //...
    
        public static function buildFromParameter(string $param)
        {
            return di_get(EntityManagerInterface::class)
                ->getRepository(self::class)
                ->find($param);
        }
    }
 ```
 
2. Extend the `AbstractResponseController` and overwrite the `transform()` method.
 
 ```php
    use eArc\Router\AbstractResponseController as BaseController;
    use eArc\Router\Interfaces\RouterEventInterface;
    
    abstract class AbstractResponseController extends BaseController
    {
        protected function transform(RouterEventInterface $event, int $pos, ReflectionType $type)
        {
            $value = parent::transform($event, $pos, $type);
    
            if (is_string($value)) {
                $name = $this->transformSpecialName($type->getName());
    
                if (class_exists($name)) {
                    try {
                        $entity = di_get(EntityManagerInterface::class)
                            ->getRepository($name)
                            ->find($value); 
    
                        return !is_null($entity) || $type->allowsNull() ? $entity : $value;
                    } catch (\Throwable $throwable)  {
                         return $value;
                    }                      
                }        
            }
        
            return $value;   
        }
    }
```

You can mix both. The interface approach is a little more efficient. The 
overwriting approach hides the logic, which is no concern in the everyday 
use, better.
 
Hint: It is an architectural decision which controller type to prefer. It depends 
on your project. Traditional web apps should use the `AbstractResponseController` 
whereas event driven designs may prefer the `AbstractController`.

### The router event

The router event is always build with an url, request method and variables. Thus 
each router event instance is always bound to a valid request and can be easily 
serialized and saved for later use if necessary. By passing additional parameters 
to the event you can overwrite the url, the request method and the variables.

```php
use eArc\Router\RouterEvent;

$event = new RouterEvent(
    $url,
    $requestMethod,
    $requestVariables
);

$event->dispatch();
```

This gives you the freedom to do whatever pre processing you need, rebuild a saved 
request or simulate one for some integration tests.

If not supplied or passed as `null` value the url is set to the path extracted 
from the `$_SERVER['REQUEST_URI']`. The default of the request method is the value 
of the `SERVER['REQUEST_METHOD']`. The request variables are set via an auto import 
of the 'INPUT_*' variables if not passed to the constructor. 

Every router event carries the information about the request and the route. They 
are saved in a request immutable (access via `$event->getRequest()`) and a route 
immutable (access via `$event->getRoute()`). For details consult the 
`eArc\Router\Interface\RouteInformationInterface` and the 
`eArc\Router\Interface\RequestInformationInterface`.

### Routes with special characters

The namespace constrains on characters (especially not allowing `-`, `.` and `~`) 
limits the usable route names. The fastest solution is to use the `.redirect` 
directive of the earc/event-tree. Place a plain text file named `.redirect` in 
the parent directory. Suppose you want to use the url `/spe~ci-al.chars` but you
use the namespace compatible substitute `/special_characters`. Then put in the 
`routing` directory the following file:

```
spe~ci-al.chars ~/special_characters
special_characters
``` 

Now the route `/spe~ci-al.chars` is represented by the directory 
`routing/special_characters`.

The `.redirect` directive is explained in the 
[The redirect directive](#the-redirect-directive) section in detail.

## Advanced usage

### Pre and post-processing 

There are a tons of examples where logic needs to be executed before or after
the controller logic. They can be divided into three cases.
1. The logic is specific to one route/controller.
2. The logic is specific to a sub route/set of controllers.
3. The logic is useful for all or nearly all controllers.

#### Via listeners attached to the route

The first two cases can use the fact that the earc/router uses the earc/event-tree 
package. The route events travel from the `routing` folder to the targeted controller 
and can be intercepted via listeners. The router event triggers all listeners that
implement the `eArc\Router\Interface\RouterListenerInterface`.

Lets start with the second case first.

Suppose you want to check the admin privileges for all routes starting with `/admin`. 
Simply put a class in the `routing/admin` folder that implements the  `RouterListenerInterface` 
and process the event analogue to the controller. You can even kill the event if 
it shall not reach any controller. (For details consult the documentation of 
[earc/event-tree](https://github.com/Koudela/eArc-eventTree).) Of course the listener 
can dispatch a new route event that is targeting `/login` or 
`/error-pages/access-denied`.

```php
namespace NamespaceOfYour\EventTreeRoot\routing\admin\user\edit;

use eArc\Router\Interfaces\RouterListenerInterface;
use eArc\Router\RouterEvent;
use eArc\Router\Interfaces\RouterEventInterface;

class Listener implements RouterListenerInterface
{
    public function process(RouterEventInterface $event) : void
    {
        //... your listener code goes here

        //... 
        // the user is not logged in
        $event->getHandler()->kill();
        (new RouterEvent('/login'))->dispatch();
        // ...
        // the user has no admin privileges
        $event->getHandler()->kill();
        (new RouterEvent('/error-pages/access-denied', 'GET'))->dispatch();
        //...
    }
}
```

Please note a new router event does not redirect the browser client. It simply
changes the flow of the apps request processing. To make an redirect change the 
path from `/login` to `/redirect/login` and place into the `routing/redirect` folder
an listener or controller that makes an redirect.

As you may have noticed you can use the router event to decouple your logic in a very
transparent way.

Let us look at the second case now.

You can simply put a listener in the same directory as the controller and it will
be called. In order to tell the router that it shall be called before or after the 
controller it has to implement the `eArc\EventTree\Interfaces\SortableListenerInterface`. 
If `getPatience()` returns a positive float it will be called after the controller
and vice versa as the controller has a patience of 0. If you have more than one 
listener in one directory you can specify an order by the `SortableListenerInterface`
too. If you want to make it configurable use the `di_param` function of the earc/di 
package in the return statement.

If a listener shall be called only if the route is targeting the controller, the 
listener has to implement the `eArc\EventTree\Interfaces\PhaseSpecificListenerInterface`
and return the `ObserverTree::PHASE_DESTINATION` constant. 

```php
namespace NamespaceOfYour\EventTreeRoot\routing\some\route;

use eArc\EventTree\Transformation\ObserverTree;
use eArc\EventTree\Interfaces\PhaseSpecificListenerInterface;
use eArc\Router\Interfaces\RouterListenerInterface;
use eArc\EventTree\Interfaces\SortableListenerInterface;
use eArc\Router\Interfaces\RouterEventInterface;

class Listener implements RouterListenerInterface, SortableListenerInterface, PhaseSpecificListenerInterface
{
    public function process(RouterEventInterface $event) : void
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

To learn more of listener `patience` and event `phases` consult the documentation of
the [earc/event-tree](https://github.com/Koudela/eArc-eventTree).

#### Via live cycle hooks

The simplest way to implement the third case and hook into the live cycle of all 
controllers is to extend your controllers from your own base controller. You can 
do pre and post processing, log exceptions or implement the old style of action 
handling - having many actions in one controller.

```php
namespace NamespaceOfYour\EventTreeRoot\routing\admin\user\edit;

use eArc\Router\AbstractController;
use eArc\Router\Interfaces\RouterEventInterface;

abstract class AbstractDeprecatedSyntaxController extends AbstractController
{
    public function process(RouterEventInterface $event) : void
    {
        $this->preProcessing($event);

        try {
            $actionId = $event->getRoute()->getParam(0);
            $methodName = $actionId.'Action';
            $this->$methodName($event);
        } catch (\Exception $exception) {
            $this->logException($exception);       
        }

        $this->postProcessing($event);
    }
    
    protected function logException(\Exception $exception) {/*...*/}

    protected function preProcessing(RouterEventInterface $event) {/*...*/}

    protected function postProcessing(RouterEventInterface $event) {/*...*/}
}
```

- **Pro**: It is simple and fast.
- **Contra**: It is not flexible. For example if you have a core app and many client 
apps extending the core app, there is no way the clients can change the flow. 
Even such basic overwriting techniques as decoration or blacklisting can not be 
applied to the base controller without decorating every single controller.

Perhaps you heard of the open-closed-principle 
([OCP](https://en.wikipedia.org/wiki/Open%E2%80%93closed_principle)) already. As 
you have seen above inheritance is not suitable to follow the OCP on a large scale. 
The program flow is not open for modification anymore. To overcome this earc/router
exposes the calling of the listeners/controllers on the event tree.

Extend your event tree root by a folder named `earc`, `earc/lifecycle` and
`earc/lifecycle/router`. If you place in the last folder a class implementing
the `eArc\EventTree\Interfaces\ListenerInterface` you can intercept the 
`eArc\Router\LifeCycle\RouterLifeCycleEvent`. 

Lets do the above example again using the force of the event tree.

We need to put three classes into the `earc/lifecycle/router` folder:

```php
namespace NamespaceOfYour\EventTreeRoot\earc\lifecycle\router;

use eArc\Observer\Interfaces\ListenerInterface;
use eArc\EventTree\Interfaces\SortableListenerInterface;
use eArc\Observer\Interfaces\EventInterface;
use eArc\Router\AbstractController;
use eArc\Router\LifeCycle\RouterLifeCycleEvent;
use eArc\Router\Interfaces\RouterEventInterface;

class PreProcessingListener implements ListenerInterface, SortableListenerInterface
{
    public function process(EventInterface $event) : void
    {
        if ($event instanceof RouterLifeCycleEvent) {
            $this->preProcessing($event->routerEvent);
        }
    }

    protected function preProcessing(RouterEventInterface $event) {/*...*/}
        
    public static function getPatience() : float
    {
        return -1;
    }
}

class PostProcessingListener implements ListenerInterface, SortableListenerInterface
{
    public function process(EventInterface $event) : void
    {
        if ($event instanceof RouterLifeCycleEvent) {
            $this->postProcessing($event->routerEvent);
        }
    }
      
    protected function postProcessing(RouterEventInterface $event) {/*...*/}

    public static function getPatience() : float
    {
      return 1;
    }
}

class ExecuteCallListener implements ListenerInterface
{
    public function process(EventInterface $event) : void
    {
        if ($event instanceof RouterLifeCycleEvent) {
            try {
                $listener = $event->listenerCallable[0];
                if (!$listener instanceof AbstractController) {
                    call_user_func($event->listenerCallable, $event->routerEvent);
                
                    return;
                }

                $actionId = $event->routerEvent->getRoute()->getParam(0);
                $methodName = $actionId.'Action';
                $listener->$methodName($event->routerEvent);
            } catch (\Exception $exception) {
                $this->logException($exception);       
            }
        }
    }
    
    protected function logException(\Exception $exception) {/*...*/}
}
```

And last but not least we blacklist the original `ExecuteCallListener` in the
configuration section. As the controllers should not be called twice.

```php
use eArc\RouterEventTreeRoot\earc\lifecycle\router\ExecuteCallListener;

di_import_param(['earc' => ['event_tree' => ['blacklist' => [
    ExecuteCallListener::class => true,
]]]]);
```

Now our logic is open (for extension) and closed (for modification) on a app
inheritance scale.

Instead of blacklisting the `ExecuteCallListener` you can decorate
him if you prefer. 
```php
use NamespaceOfYour\App\Somewhere\OutsideTheEventTree\ExecuteCallListener;
use eArc\RouterEventTreeRoot\earc\lifecycle\router\ExecuteCallListener as OriginECL;

di_decorate(OriginECL::class, ExecuteCallListener::class);
```
    
Notes: 
1. Decoration can be done anywhere in the code prior to the call but blacklisting 
has to be done before the first event ist dispatched.  
2. In the case of decoration you have to place the decorating `ExecuteCallListener`
outside the event tree.

### Routing/event tree inheritance

As you may have noticed, the original `ExecuteCallListener` lives outside of *your*
event tree root directory as part of the vendor directory but is called by the event.
This is what is called event tree inheritance. If you consult your configuration
you will notice there are two `earc.event_tree.directories`. Yours and the 
`earc/router/earc-event-tree`. If you take the two event trees and combine them
at their roots you get the event tree that is actual used. You can combine as
many trees as you want. A leaf exists if it exists in at least one tree.

The routing part is combined too, not surprisingly though. It gives you the ability
to define routes package wise. 

Event tree inheritance is a mighty tool but can be confusing for beginners. You
can use the `view-tree` command line tool of the earc/event-tree package
to draw (and `grep`) a representation of the actual tree.

### Customized events

To keep your components decoupled the event should be the only place where 
runtime information is kept (when a listener/controller has finished his work).
As the runtime information is app specific it is part of your architectural
responsibility to design your own events.

Best practice is to use interfaces to describe the runtime information. Follow
the interface segregation principle 
([ISP](https://en.wikipedia.org/wiki/Interface_segregation_principle)). Design
objects that implement the interface(s) and extend the `eArc\Router\RouterEvent` 
to provide these objects.

```php
namespace NamespaceOfYour\App\Somewhere\OutsideTheEventTree;

use eArc\Router\RouterEvent;

interface AppRuntimeInformationInterface
{
    public function getRunnerId(): int;
    public function addWarning(Exception $exception);
    public function getWarnings(): array;
    public function getSession(): SessionInterface;
    public function getCurrentUser() : ?UserInterface;
    public function setCurrentUser(?UserInterface $user);
}

class AppRuntimeInformation implements AppRuntimeInformationInterface
{
    protected $runnerId;
    protected $warnings = [];
    protected $session;
    protected $currentUser;

    //...
}

class AppRouterEvent extends RouterEvent
{
    protected $runtimeInformation;

    public function __construct(?string $uri = null,?string $requestMethod = null,?array $argv = null)
    {
        parent::__construct($uri,$requestMethod,$argv);

        $this->runtimeInformation = di_get(AppRuntimeInformation::class);              
    }

    public function getRI(): AppRuntimeInformationInterface {/*...*/}
}
```

Don't forget to replace the router event in your bootstrap section.

```php
$event = new AppRouterEvent();
$event->dispatch();
```

Now all runtime information that has to be exchanged between your listeners/controllers 
is exposed, easy to find and easy to understand.

### Subsystem handling

If you need a router event that triggers only a subset of listeners/controllers,
you can modify the `getApplicableListener()` method provided by the `EventInterface`.
It returns an array of all listener interfaces that are called by the event.

For example if a core app supports several versions you can use separate listeners/controllers 
for different versions this way. If a controller supports more than one 
version it simply implements more than one listener interface. 

Other use cases where this functionality comes handy: 
- Some part of the app is only available in some country or to some language.
- Some part of the app is only active in debug mode.
- The app behaviour changes significantly for power users paying more money.
- Different parts of the app can be toggled.
- Different phases of processing routes.

### Further decoupling

You can use both the event tree and the router tree (you can look at it as a 
subset of the event tree) for a better decoupling of your code. Lets do one more
example.

Nearly all web apps do some sort of rendering. Rendering need three things come
together: The data, the template and the engine. The data does not change on behalf
of visualisation, the template and the engine does.

The controllers control the data generation and persistence mechanisms of your
app, they should not control the visualisation layer too. The underlying principle 
is the famous single-responsibility principle 
([SRP](https://en.wikipedia.org/wiki/Single-responsibility_principle)).

Therefore it is a bad practice to inject a template engine into a controller or
use a template annotation within. The controller should not know about these things.

The route determines the controller and together with some parameter the data, 
but in old fashioned frameworks like symfony it seems that it also determines the 
template. That is not correct and should not be the way you code. I have seen 
uncountable examples where two actions/routes do the same thing generating the 
same response data just to get access to different templates or return a json 
representation instead of a template representation of the data. 

Think of it a bit and you realize that the different routes are just different presentation 
parameters in these cases. Old fashioned MVCs does not support decoupling very well, 
so the programmers need to get inventive in a bad way.

How can we do better?

Every controller returns data that is very specific. You shouldn't return it as array,
you should return it as object. Once you have an object you have an identifier for
the collection of templates the app can use. If there is more than one template
available in this collection the representation parameter comes into play. Remember
the parameter are part of the event, but the representation parameter is a valid 
part of the returned data too - possibly transformed by the controller.

Once the controller has processed there is the data and the keys to choose from
the templates a single one. All attached to the event. That smells for post processing!

After implementing we can change the chosen template by just changing the 
assignment of the object class, representation parameter and template. If we want 
to change the template engine, we just need to decorate one listener. We do not 
need to change the code of all controllers or need to comply to an engine interface 
that does not fit to our new use case.

Hint: It might be a good idea to organize your templates in the same or similar
directory structure as the returned objects. This way you reduce the configuration 
overhead significantly. Note that this directory structure can be completely different
from the routing directory structure.

### Customized routes

#### Rewriting of routes

There are several reasons for a route to change. Backward compatibility, customer 
request or SEO are just three of it. On a app without routing tree inheritance 
(event tree inheritance on the routing part) it is easy. Just rename and restructure 
the folders.

##### The redirect directive

If you need the same content under different routes you should use the `.redirect`
directive of the earc/event-tree. It is just a file named `.redirect`. You can place
it in every folder where redirection should take place. Every line is a redirection.
At the beginning of the line you put the sub folder name you want to redirect 
(it does not need to exist) and at second place separated by a blank you put the
target path relative to the event trees root directory. `~/` is a shortcut to
reference the current directory.

To exclude an existing or inherited directory just leave the target empty. `.redirect`
directives are part of the event tree inheritance. If several `.redirect` directives
of the same path exists naming the same sub folder the ordering of the 
´earc.event_tree.directories´ is important. The directives are overwritten in
the order their directory tree are registered. You can use the target shortcut `~` 
to cancel an redirect. 

```
lama creatures/animals/fox #redirects events targeting the lama subfolder to creatures/animals/fox
eagle ~/extinct/2351       #redirects events targeting the eagle subfolder to the extinct/2351 subfolder
maple                      #hides the maple subdirectory from the events
tiger ~                    #cancels all redirects for tiger made by the event trees inherited so far
```

For example the rewrite of the route `/imported/products` to `/products/imported` would
take two steps (for each rewritten part one):

1 ) Place into the `routing` directory the `.redirect` directive
```
products routing/imported
imported
```

2 ) Place into the `imported` directory the `.redirect` directive
```
imported ~/products
products
```

Obviously using this changes the directory arguments of the route but it does not 
change the called controller or the order the listeners are called.

To rewrite the base leafs put the `.redirect` directive into the event tree root.

##### The lookup directive

Every `.redirect` directive you use destroys a bit of the clarity the explicit
design of the event tree gives to you. Therefore making massive use of the `.redirect` 
directive is an anti pattern. If you need to redirect quite a bit of the tree
it is better to rewrite it and use the `.lookup` directive to include the listeners
of the old tree.

Like the `.redirect` directive the `.lookup` directive is a plain text file. 
If you put a path in there it will be included. That means every listener in the 
linked leaf of the event tree will be handled as if it would reside in the current
leaf. Every line is an separate include. The path has to be relative to the event
tree root.

##### The routing directory

If you rewrite a routing tree it is a best practice to use a new routing directory.
If you don't, you will need beside the `.lookup` directive (which is easy to understand)
the `.redirect` directive (which can be far more confusing) to exclude unwanted 
routing leafs. 

To achieve this just configure a new routing dir for your routing event. 

```php
use eArc\Router\Interfaces\RouterEventInterface;

di_import_param(['earc' => [
    'router' => [
        'routing_directory' => [
            RouterEventInterface::class => 'v2/routing'
        ]   
    ]
]]);
```

You can define different routing directories for different events. The first key
the event passes an `instanceof` check against defines the routing directory. If 
none passes the routing directory is `routing`. 

#### Mapping routes

There are some use cases where a mapping is superior to the concepts of the earc 
router. Think of a multinational site prefixing the routes with the language key
like `/en`, `/de` or `/fr`. Then you could make sub directories to the routing
directory named `en`, `de` and `fr` and place in every dir the `.lookup` for a 
controller who sets the language locale and makes the rerouting. There is nothing
wrong with that but its much more efficient to extract the language and cut down 
the route by a few lines of code. 

### Serializing events

Routing events can be serialized. 

This makes it easy as drinking a cup of tea to check the access rights of 
users and reroute them after they logged in or have registered.

However note that unserialized events need to be dispatched again. They lose their
position in the routing tree and begin their travel from the routing directory.
This way it is really hard to mess it up.  

### Caching the routing tree

The concept of the routing tree is deeply rooted in the file system. Filesystem 
access is not cheap in terms of time and can be a bottle neck. If you use a file 
cache like [ACPu](https://www.php.net/manual/en/book.apcu.php) it is worth 
considering loading the event tree structure (even if it may be huge) into memory.

The configuration steps are described in the earc/event-tree 
[documentation](https://github.com/Koudela/eArc-eventTree#performance-optimization).

## Further reading 

- Since the earc/router is build on top of the [earc/event-tree](https://github.com/Koudela/eArc-eventTree) 
please feel free to consult the earc/event-tree documentation.

- To take full advantage of the global container free dependency injection system
[earc/di](https://github.com/Koudela/eArc-di) reading its documentation might 
be a good idea. 

## Releases

### Release 3.1

- `AbstractResponseController` supports type hints for [earc/data](https://github.com/Koudela/eArc-data) entities

### Release 3.0

- PHP ^7.3 || PHP ^8.0

### Release 2.1

- response controller
- parameter injection
- parameter transformer
- `ParameterFactoryInterface`
- `AbstractResponseController::USE_REQUEST_KEYS` #TODO Documentation
- response controller default value #TODO Documentation

### Release 2.0

- bootstrap via [earc/core](https://github.com/Koudela/eArc-core)
- caching of the routing tree

### Release 1.1

- `RequestInformationInterface::getArg()` and `RouteInformationInterface::getParam()`
now accept a default parameter

### Release 1.0

- The route is now matched against the `routing` part of an 
[earc/event-tree](https://github.com/Koudela/eArc-eventTree).
- The dispatcher is now part of earc/router instead of earc/core and dispatches 
an earc/event-tree event.
- Introduces the immutable objects `Route` and `Request`. Both are attached as 
payload to the dispatched earc/event-tree event.
- There are no access and main-controller anymore. Controller are now
disguised earc/event-tree listener.
- The router live cycle is exposed via an event tree. Making it easy to implement
pre and post processing while following the open-closed-principle on a large scale.

### Release 0.1

The first official release.
