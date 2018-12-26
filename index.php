<?php
use MaxBeckers\AmazonAlexa\Helper\ResponseHelper;
use MaxBeckers\AmazonAlexa\Request\Request;
use MaxBeckers\AmazonAlexa\RequestHandler\RequestHandlerRegistry;
use MaxBeckers\AmazonAlexa\Validation\RequestValidator;
require 'vendor/autoload.php';
require 'handlers/HelpRequestHandler.php';
require 'handlers/JustForTodayRequestHandler.php';
require 'handlers/LaunchRequestHandler.php';
include_once 'config.php';
include_once 'functions.php';

$requestBody = file_get_contents('php://input');
error_log($requestBody);
if ($requestBody) {
    $alexaRequest = Request::fromAmazonRequest($requestBody, $_SERVER['HTTP_SIGNATURECERTCHAINURL'], $_SERVER['HTTP_SIGNATURE']);
    // Request validation
    $validator = new RequestValidator();
    $validator->validate($alexaRequest);
    // add handlers to registry
    $responseHelper         = new ResponseHelper();
    $requestHandlerRegistry = new RequestHandlerRegistry([
        new HelpRequestHandler($responseHelper),
        new LaunchRequestHandler($responseHelper),
        new JustForTodayRequestHandler($responseHelper)
    ]);
    // handle request
    $requestHandler = $requestHandlerRegistry->getSupportingHandler($alexaRequest);
    $response       = $requestHandler->handleRequest($alexaRequest);
    // render response
    header('Content-Type: application/json');
    echo json_encode($response);
}
exit();
