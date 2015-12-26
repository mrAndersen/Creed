<?php

require_once('../vendor/autoload.php');

$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();



$loader = new Twig_Loader_Filesystem('../src/views');
$twig = new Twig_Environment($loader);
$view =  $twig->render('index.html.twig');


$response = new \Symfony\Component\HttpFoundation\Response($view);
$response->send();

