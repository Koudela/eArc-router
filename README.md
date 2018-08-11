# eArc router

Router component of the [eArc framework](https://github.com/Koudela/eArc-core).

The eArc framework does not define any routes - they are expressed via the
filesystem.

The eArc router decomposes an url path in parameters matching the maximal path
in the routing dir containing a main controller (real arguments) and a the rest
(virtual arguments). Registers the main controller and all files named
access.php along the path as access controllers.

Given this direct mapping between the url and the directory structure,
understanding the apps routing process is as simple as typing `tree` at the base
of the routing directory.

## Installation

If you want to use the eArc router without the eArc framework, you can install
the component via composer.

```
$ composer install earc/router
```

Hint: If you want to install the eArc framework use the
[earc/minimal package](https://github.com/Koudela/eArc-minimal).

## Basic Usage

The router instance is always constructed with request method and url. Thus each
instance is always bound to a valid request and can be easily serialized and
saved for later use if necessary. 

```php
use eArc\router\Router;

$router = new Router(
    '/absolute/path/to/your/routing/base/dir',
    $_SERVER['REQUEST_METHOD'],
    isset($_GET['url']) ? $_GET['url'] : '/',
    null
);
```

The router exposes nine methods for retrieving the calculated information.

```php
$router->getRequestType(); // string ('GET', 'POST', 'PUT', 'PATCH', 'DELETE', ...) 


$router->cntRealArgs(); // int (number real parameter) 

$router->getRealArg(int $pos); // ?string (real parameter at positon $pos)

$router->getRealArgs(); // array (all real parameter - key === position)


$router->cntVirtualArgs(); // int (number virtual parameter)

$router->getVirtualArg(int $pos); // ?string (virtual parameter at position $pos)

$router->getVirtualArgs(): // array (all virtual parameter - key === position)


$router->getAbsolutePathsToAccessControllers(); // array of strings

$router->getAbsolutePathToMainController(); // string
```

## Example

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

To deepen the understanding of the power of this routing concept reading the 
chapter about the access controllers in the 
[eArc core manual](https://github.com/Koudela/eArc-core#the-access-controllers)
might be a good idea. 

## Advanced Usage  

### Using controller linked to the request type

Instead of using main.php as controller hint you can use get.php for every GET
request, post.php for every POST request, put.php for every PUT request and so
on. That might be handy if you designing a RESTfull or RESTlike interface or 
if you want to have some routes only accessed by POST for security reasons*. 

If both files main.php and get.php available then get.php is used for GET
requests and main.php for all other requests. The same applies to the other 
request types.  

**(Consider a posting that gets reviewed by an admin containing an 
image `<img src="http://example.com/office/admin/user/200/delete" />`. This 
could be disastrous if it is allowed to access this route with GET.)*

### Using your own main controller names

If you wish to use your own main controller names you must pass an array as
fourth argument to the constructor. Thereby the request method has to map to an
array of possible main controller names.
```php
use eArc\router\Router;

$router = new Router(
    '/absolute/path/to/your/routing/base/dir',
    $_SERVER['REQUEST_METHOD'],
    isset($_GET['url']) ? $_GET['url'] : '/',
    [
        'GET' => ['1.php', 'request.php', 'controller.php'], 
        'POST' => ['2.php', 'request.php', 'controller.php'],
        'FANCY' => ['rewind.php', 'unicorn.php', 'controller.php'],
        'DELETE' => ['del.php', 'controller.php']
        'COMMAND' => ['command.php']
    ]
);
```
If the maximal matching path has more than one possible main controller the
left most matching controller is used.      

## Releases

### release v0.1

the first official release
