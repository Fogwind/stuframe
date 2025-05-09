<?php /* v 0.0.1
$name = $_GET['name'] ?? 'World';
printf('Hello %s', $name);
*/
?>
<?php /* v 0.0.2
$name = $_GET['name'] ?? 'World';

header('Content-Type: text/html; charset=utf-8');

printf('Hello %s', htmlspecialchars($name, ENT_QUOTES, 'UTF-8'));
*/
?>
<?php 
require_once __DIR__.'/vendor/autoload.php';
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$request = Request::createFromGlobals();

$name = $request->query->get('name','World');
$response = new Response(sprintf('Hello %s',htmlspecialchars($name,ENT_QUOTES,'utf-8')));

$response->send();
?>