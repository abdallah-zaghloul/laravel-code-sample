<?php

namespace App\Enums;
use Symfony\Component\HttpFoundation\Response as Code;


enum HTTPCodeEnum: int
{
    case UNPROCESSABLE_ENTITY = Code::HTTP_UNPROCESSABLE_ENTITY;
    case UNAUTHORIZED = Code::HTTP_UNAUTHORIZED;
    case FORBIDDEN = Code::HTTP_FORBIDDEN;
    case NOT_FOUND = Code::HTTP_NOT_FOUND;
    case INTERNAL_SERVER_ERROR = Code::HTTP_INTERNAL_SERVER_ERROR;
    case OK = Code::HTTP_OK;
}
