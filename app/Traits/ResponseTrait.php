<?php

namespace App\Traits;

use GuzzleHttp\Exception\RequestException;

trait ResponseTrait
{
    public static function successResponse($message = 'Success', $data = null, $code=200)
    {
        $res = [
            'status' => true,
            'message' => $message
        ];
        if ($data) {
            $res['data'] = $data;
        }
        return response()->json($res, $code);
    }

    public static function failureResponse($message = 'Error Occurred', $data = [], $code=500)
    {
        $res = [
            'status' => false,
            'message' => $message
        ];
        if (count($data)) {
            $res['data'] = $data;
        }
        return response()->json($res, $code);
    }

    public static function exceptionResponse(\Exception $exception, $module = '')
    {

        if ($exception instanceof \Illuminate\Database\QueryException) {
            $message = $exception->errorInfo;
        } else if ($exception instanceof RequestException) {
            return json_decode($exception->getResponse()->getBody()->getContents(), true);
        } else {
            $message = $exception->getMessage();
        }

        return response()->json([
            'status' => false,
            'error' => $message,
            'file' => $exception->getFile(),
            'code' => $exception->getCode(),
            'line' => $exception->getLine(),
            'message' => "Exception in module: {$module}",
            'details' => $exception->getTraceAsString()
        ], 500);
    }

    public static function errorResponse($module = '', $error = 'create', $hint = '', $code = 409, $data = [])
    {
        return response()->json([
            'error' => self::errorType($error),
            'code' => $code,
            'hint' => $hint,
            'message' => "Can't {$error} {$module}",
            'data' => $data
        ], $code);
    }

    private static function errorType($error)
    {
        switch ($error) {
            case 'create':
                return 'not_be_created';
            case 'update':
                return 'not_be_updated';
            case 'delete':
                return 'not_be_deleted';
            default:
                return ((empty($error)) ? 'Unknown' : $error);
        }
    }
}
