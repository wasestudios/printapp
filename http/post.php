<?php
# Seguridad
defined('INDEX_DIR') OR exit('was software says .i.');
$app->post('/print/comanda',function($request, $response){
  $e = new Prt();
  $response->withJson($e->printComanda($_POST));
  return $response;
});

$app->post('/print/venta',function($request, $response){
  $e = new Prt();
  $response->withJson($e->printVenta($_POST));
  return $response;
});

$app->post('/print/cuenta',function($request, $response){
  $e = new Prt();
  $response->withJson($e->printCuenta($_POST));
  return $response;
});