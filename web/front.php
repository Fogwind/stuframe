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


$request = Request::createFromGlobals();


$map = [
    '/hello' => 'hello',
    '/bye'   => 'bye',
];

$path = $request->getPathInfo();

if(isset($map[$path])) {
    ob_start();
    // https://www.php.net/manual/zh/function.extract.php
    extract($request->query->all(), EXTR_SKIP);
    include sprintf(__DIR__.'/../src/pages/%s.php', $map[$path]);
    $response = new Response(ob_get_clean());
} else {
    $response = new Response('Not Found',404);
}

$response->send();
?>