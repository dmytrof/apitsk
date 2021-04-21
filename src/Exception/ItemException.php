<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ItemException extends \Exception implements HttpExceptionInterface
{
    /**
     * @inheritDoc
     */
    public function getStatusCode()
    {
        return Response::HTTP_BAD_REQUEST;
    }

    /**
     * @inheritDoc
     */
    public function getHeaders()
    {
        return [];
    }
}