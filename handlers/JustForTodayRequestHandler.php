<?php

use MaxBeckers\AmazonAlexa\Helper\ResponseHelper;
use MaxBeckers\AmazonAlexa\Request\Request;
use MaxBeckers\AmazonAlexa\Request\Request\Standard\IntentRequest;
use MaxBeckers\AmazonAlexa\RequestHandler\AbstractRequestHandler;
use MaxBeckers\AmazonAlexa\Response\Card;
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
        $result = get("https://jftna.org/jft/");
        $stripped_results = strip_tags($result);
        $without_tabs = str_replace("\t", "", $stripped_results);
        $without_htmlentities = html_entity_decode($without_tabs);
        $without_extranewlines = preg_replace("/[\r\n]+/", "\n\n", $without_htmlentities);
        return $this->responseHelper->respond($without_extranewlines, true);
    }
}
