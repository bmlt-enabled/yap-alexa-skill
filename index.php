<?php
use MaxBeckers\AmazonAlexa\Helper\ResponseHelper;
use MaxBeckers\AmazonAlexa\Request\Request;
use MaxBeckers\AmazonAlexa\RequestHandler\RequestHandlerRegistry;
use MaxBeckers\AmazonAlexa\Validation\RequestValidator;
require 'vendor/autoload.php';
require 'handlers/HelpRequestHandler.php';
require 'handlers/SimpleIntentRequestHandler.php';
require 'handlers/LaunchRequestHandler.php';
include_once 'config.php';

$requestBody = file_get_contents('php://input');
error_log($requestBody);
if ($requestBody) {
    $alexaRequest = Request::fromAmazonRequest($requestBody, $_SERVER['HTTP_SIGNATURECERTCHAINURL'], $_SERVER['HTTP_SIGNATURE']);
    // Request validation
    $validator = new RequestValidator();
    $validator->validate($alexaRequest);
    // add handlers to registry
    $responseHelper         = new ResponseHelper();
    $helpRequestHandler     = new HelpRequestHandler($responseHelper);
    $simpleRequestHandler = new SimpleIntentRequestHandler($responseHelper);
    $launchRequestHandler   = new LaunchRequestHandler($responseHelper);
    $requestHandlerRegistry = new RequestHandlerRegistry([
        $helpRequestHandler,
        $simpleRequestHandler,
        $launchRequestHandler
    ]);
    // handle request
    $requestHandler = $requestHandlerRegistry->getSupportingHandler($alexaRequest);
    $response       = $requestHandler->handleRequest($alexaRequest);
    // render response
    header('Content-Type: application/json');
    echo json_encode($response);
}
exit();
