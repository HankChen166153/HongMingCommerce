<?php


/**
 * Front controller
 *
 * PHP version 5.4
 */

use App\Middleware\Authenticate;
use Core\Auth;

 require __DIR__ . "/../bootstrap/app.php";

/**
 * Routing
 */
$router = new Core\Router();

$router->middleware([
    'auth' => App\Middleware\Authenticate::class
]);


// Add the routes
$router->add('', ['controller' => 'Home', 'action' => 'index']);//*
$router->add('{controller}/{action}');
$router->add('{controller}/{id:\d+}/{action}');
$router->add('admin/{controller}/{action}', ['namespace' => 'Admin']);

// $router->add('shoppingCart/api/itemList', ['controller' => 'ShoppingCartController', 'action' => 'itemList']);
// $router->add('shoppingCart/api/login', ['controller' => 'ShoppingCartController', 'action' => 'login']);
$router->post('/shoppingCart/api/fetchItem', ['middleware' => [], 'controller' => 'ShoppingCartController', 'action' => 'fetchItem']);
$router->get('/shoppingCart/api/itemList', ['middleware' => [], 'controller' => 'ShoppingCartController', 'action' => 'itemList']);
$router->post('/shoppingCart/api/login', ['middleware' => [], 'controller' => 'MemberController', 'action' => 'login']);
$router->post('/shoppingCart/api/logout', ['middleware' => [], 'controller' => 'MemberController', 'action' => 'logout']);
$router->post('/shoppingCart/api/refreshToken', ['middleware' => [], 'controller' => 'MemberController', 'action' => 'refreshToken']);
$router->post('/shoppingCart/api/register', ['middleware' => [], 'controller' => 'MemberController', 'action' => 'register']);
$router->post('/shoppingCart/api/test', ['middleware' => [], 'controller' => 'MemberController', 'action' => 'test']);
$router->post('/shoppingCart/api/userOrder', ['middleware' => [], 'controller' => 'ShoppingCartController', 'action' => 'userOrder']);
$router->post('/shoppingCart/api/checkout', ['middleware' => ['auth'], 'controller' => 'ShoppingCartController', 'action' => 'checkOut']);

// $router->dispatch($_SERVER['QUERY_STRING']);
$router->dispatch($_SERVER['REQUEST_URI']);


$router->add('posts/addNewAction', ['controller' => 'posts', 'action' => 'addNewAction']);

