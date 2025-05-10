<?php 
$name = $request->query->get('name', 'World');
$response->setContent(sprintf('Hello %s', htmlspecialchars($name,ENT_QUOTES,'utf-8')));
?>