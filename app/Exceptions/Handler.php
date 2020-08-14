<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        // This will replace our 404 response with a JSON response.
        if ($exception instanceof QueryException || $request->wantsJson()) {
            return response()->json([
                'status' => false,
                'data' => [ $exception->getMessage() ]
            ], 500);
        }

        if ($exception instanceof ModelNotFoundException || $request->wantsJson()) {
            return response()->json([
                'status' => false,
                'data' => [ 'Resource item not found.' ]
            ], 404);
        }

        if ($exception instanceof NotFoundHttpException || $request->wantsJson()) {
            return response()->json([
                'status' => false,
                'data' => [ 'Resource not found.' ]
            ], 404);
        }

        if ($exception instanceof MethodNotAllowedHttpException || $request->wantsJson()) {
            return response()->json([
                'status' => false,
                'data' => [ 'Method not allowed.' ]
            ], 405);
        }

        if($exception instanceof TokenInvalidException || $request->wantsJson()) {
            return response()->json([
                'status' => false,
                'data' => [ 'Token is invalid' ]
            ], 403);
        }

        if ($exception instanceof TokenExpiredException || $request->wantsJson()) {
            return response()->json([
                'status' => false,
                'data' => [ 'Token is expired' ]
            ], 403);
        }

        if ($exception instanceof JWTException || $request->wantsJson()) {
            return response()->json([
                'status' => false,
                'data' => [ 'Something is wrong' ]
            ], 403);
        }

        if($exception instanceof UnauthorizedHttpException || $request->wantsJson()){
            return response()->json([
                'status' => false,
                'data' => [ $exception->getMessage() ]
            ], $exception->getStatusCode());
        }

        return parent::render($request, $exception);
    }
}
