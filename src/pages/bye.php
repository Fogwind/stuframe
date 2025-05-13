<?php /* v 0.0.3
require_once __DIR__.'/vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$request = Request::createFromGlobals();

$response = new Response('Goodbye!');
$response->send();
*/
?>

<?php /* v 0.0.4
require_once __DIR__.'/init.php';

$response->setContent('Goodbye!');
$response->send();
*/
?>
<?php
//$response->setContent('Goodbye!');
?>
Goodbye!!