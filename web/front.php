<?php
// In PHP, __DIR__ is a magic constant that returns the directory of the current file. 
// When you concatenate it with '/../', you are navigating to the parent directory of the current file's directory.
// For example, with `/var/www/project/app/Example.php`
// __DIR__ evaluates to /var/www/project/app.
// __DIR__ . '/../' becomes /var/www/project/app/../, which resolves to /var/www/project.
// For clarity, you can use dirname(__DIR__) to directly get the parent directory without manually appending '/../':
// `$parentDir = dirname(__DIR__);` Same result as __DIR__ . '/../'
require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing;

function render_template($request)
{
    // https://www.php.net/manual/zh/function.extract.php
    // 提取请求数据
    extract($request->attributes->all(), EXTR_SKIP);
    ob_start();
    // 匹配到的路由保存在$_route变量中
    include sprintf(__DIR__.'/../src/pages/%s.php', $_route);
    
    return new Response(ob_get_clean());
}

$request = Request::createFromGlobals();
// 路由配置
$routes = include __DIR__.'/../src/app.php';

$context = new Routing\RequestContext();
$context->fromRequest($request);
// 根据路由配置生产路由map
$matcher = new Routing\Matcher\UrlMatcher($routes, $context);

try {
    $request->attributes->add($matcher->match($request->getPathInfo()));

    $response = call_user_func($request->attributes->get('_controller'), $request);
} catch (Routing\Exception\ResourceNotFoundException $exception) {
    $response = new Response('Not Found', 404);
} catch (Exception $exception) {
    $response = new Response('An error occurred', 500);
}

$response->send();
?>