<?php

use MaxBeckers\AmazonAlexa\Helper\ResponseHelper;
use MaxBeckers\AmazonAlexa\Request\Request;
use MaxBeckers\AmazonAlexa\Request\Request\Standard\IntentRequest;
use MaxBeckers\AmazonAlexa\RequestHandler\AbstractRequestHandler;
use MaxBeckers\AmazonAlexa\Response\Response;

class JustForTodayRequestHandler extends AbstractRequestHandler
{
    /**
     * @var ResponseHelper
     */
    private $responseHelper;

    /**
     * @param ResponseHelper $responseHelper
     */
    public function __construct(ResponseHelper $responseHelper)
    {
        $this->responseHelper          = $responseHelper;
        $this->supportedApplicationIds = [$GLOBALS['skill_id']];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsRequest(Request $request): bool
    {
        return $request->request instanceof IntentRequest && 'JustForTodayIntent' === $request->request->intent->name;
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(Request $request): Response
    {
        $result = get("https://jft.na.org");
        $without_scripts = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $result);
        $stripped_results = strip_tags($without_scripts);
        $without_tabs = str_replace("\t", "", $stripped_results);
        $without_htmlentities = html_entity_decode($without_tabs);
        $without_dividers = preg_replace('/^[\s\x{2014}\x{2013}-]+$/mu', '', $without_htmlentities);
        $without_extranewlines = preg_replace("/[\r\n]+/", "\n", $without_dividers);
        $spelled_na = preg_replace('/\bNA\b/', 'N.A.', $without_extranewlines);
        return $this->responseHelper->respond($spelled_na, true);
    }
}
