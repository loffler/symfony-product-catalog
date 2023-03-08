<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProductNotFoundException extends HttpException
{
    public function __construct(int $productId)
    {
        parent::__construct(Response::HTTP_NOT_FOUND, sprintf('Product with ID %s was not found', $productId));
    }
}