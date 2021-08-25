<?php

namespace Polly\Core;

abstract class Controller
{
    protected Response $response;

    public final function __construct(Response &$response)
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