<?php
use Symfony\Component\Routing;

$routes = new Routing\RouteCollection();
// 注册路由
$routes->add('hello', new Routing\Route('/hello/{name}',['name' => 'World']));
$routes->add('bye', new Routing\Route('/bye'));

return $routes;
?>