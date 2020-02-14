<?php

# Seguridad
defined('INDEX_DIR') OR exit('was software says .i.');

//------------------------------------------------

$app->get('/print',function($request, $response){
  $e = new Prt();
  $response->withJson($e->printComanda($_GET['data']));
  return $response;
});

