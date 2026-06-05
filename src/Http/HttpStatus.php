<?php

declare(strict_types=1);

namespace Vestige\Http;

enum HttpStatus: int
{
    case BadRequest = 400;
    case Unauthorized = 401;
    case Forbidden = 403;
    case NotFound = 404;
    case MethodNotAllowed = 405;
    case Conflict = 409;
    case UnprocessableEntity = 422;
    case TooManyRequests = 429;
    case InternalServerError = 500;
    case ServiceUnavailable = 503;

    public function reasonPhrase(): string
    {
        return match ($this) {
            self::BadRequest => 'Bad Request',
            self::Unauthorized => 'Unauthorized',
            self::Forbidden => 'Forbidden',
            self::NotFound => 'Not Found',
            self::MethodNotAllowed => 'Method Not Allowed',
            self::Conflict => 'Conflict',
            self::UnprocessableEntity => 'Unprocessable Entity',
            self::TooManyRequests => 'Too Many Requests',
            self::InternalServerError => 'Internal Server Error',
            self::ServiceUnavailable => 'Service Unavailable',
        };
    }

    public function isClientError(): bool
    {
        return match ($this) {
            self::BadRequest,
            self::Unauthorized,
            self::Forbidden,
            self::NotFound,
            self::MethodNotAllowed,
            self::Conflict,
            self::UnprocessableEntity,
            self::TooManyRequests => true,
            self::InternalServerError,
            self::ServiceUnavailable => false,
        };
    }

    public function isServerError(): bool
    {
        return match ($this) {
            self::InternalServerError,
            self::ServiceUnavailable => true,
            self::BadRequest,
            self::Unauthorized,
            self::Forbidden,
            self::NotFound,
            self::MethodNotAllowed,
            self::Conflict,
            self::UnprocessableEntity,
            self::TooManyRequests => false,
        };
    }
}
