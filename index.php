<?php
use MaxBeckers\AmazonAlexa\Helper\ResponseHelper;
use MaxBeckers\AmazonAlexa\Request\Request;
use MaxBeckers\AmazonAlexa\RequestHandler\RequestHandlerRegistry;
use MaxBeckers\AmazonAlexa\Validation\RequestValidator;
require 'vendor/autoload.php';
require 'handlers/HelpNeededRequestHandler.php';
require 'handlers/JustForTodayRequestHandler.php';
require 'handlers/LaunchRequestHandler.php';
require 'handlers/FindMeetingRequestHandler.php';
require 'handlers/FallbackRequestHandler.php';
require 'handlers/StopRequestHandler.php';
require 'handlers/CancelRequestHandler.php';
require 'handlers/SessionEndedRequestHandler.php';
include_once 'config.php';
include_once 'functions.php';

$requestBody = file_get_contents('php://input');
if ($requestBody) {
    try {
        $alexaRequest = Request::fromAmazonRequest($requestBody, $_SERVER['HTTP_SIGNATURECERTCHAINURL'], $_SERVER['HTTP_SIGNATURE']);
        $validator = new RequestValidator();
        $validator->validate($alexaRequest);
        $responseHelper = new ResponseHelper();
        $requestHandlerRegistry = new RequestHandlerRegistry([
            new HelpNeededRequestHandler($responseHelper),
            new LaunchRequestHandler($responseHelper),
            new FindMeetingRequestHandler($responseHelper),
            new JustForTodayRequestHandler($responseHelper),
            new FallbackRequestHandler($responseHelper),
            new CancelRequestHandler($responseHelper),
            new StopRequestHandler($responseHelper),
            new SessionEndedRequestHandler($responseHelper)
        ]);
        // handle request
        $requestHandler = $requestHandlerRegistry->getSupportingHandler($alexaRequest);
        $response = $requestHandler->handleRequest($alexaRequest);

        // render response
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    catch (Exception $ex) {
        http_response_code(400);
    }
}
exit();
