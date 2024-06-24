<?php

namespace Polly\Core;

abstract class Controller
{
    protected ?Response $response = null;

    public function __construct(?Response &$response = null)
    {
        $this->response = $response;
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }


}
