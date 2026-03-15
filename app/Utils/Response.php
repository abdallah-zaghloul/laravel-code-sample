<?php

namespace App\Utils;

use Illuminate\Http\Exceptions\HttpResponseException;
use App\Enums\HTTPCodeEnum;
use function Illuminate\Support\enum_value;


trait Response
{
    /**
     * Summary of success
     * @param mixed $message
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public static function success(?string $message = null)
    {
        return response()->json(
            data: [
                "message" => $message ?? trans("response.success"),
                "type" => "success",
            ],
            status: enum_value(HTTPCodeEnum::OK)
        );
    }


    /**
     * Summary of data
     * @param string $type
     * @param mixed $data
     * @param string|null $message
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public static function data(string $type, mixed $data, ?string $message = null)
    {
        return response()->json(
            data: [
                "message" => $message ?? trans("response.success"),
                "type" => $type,
                $type => $data
            ]
        );
    }


    /**
     * Summary of error
     * @param HTTPCodeEnum|int $code
     * @param mixed $errors
     * @param mixed $message
     * @param mixed $type
     * @param bool $shouldThrow
     */
    public static function error(
        HTTPCodeEnum|int $code,
        mixed $errors = [],
        ?string $message = null,
        ?string $type = "error",
        bool $shouldThrow = true
    ) {

        $response = response()->json(
            data: collect([
                "message" => $message ?? trans("response.error"),
                "type" => $type,
            ])->when(
                    filled($errors),
                    fn($data) => $data->put($type, $errors)
                ),
            status: enum_value($code)
        );

        if ($shouldThrow)
            throw new HttpResponseException($response);

        return $response;
    }
}
