<?php

namespace App\Traits;

use Illuminate\Http\Request;


trait RequestTrait
{
    /**
     * @param $uri
     * @param string $method
     * @param array $parameters
     * @param array $headers
     * @param array $cookies
     * @param array $files
     * @param array $server
     * @param null $content
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public static function request($uri, $method = 'GET', $parameters = [], $headers = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $server = array_replace([
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_ACCEPT' => 'application/json'
        ], $server);
        $tokenRequest = Request::create(
            $uri,
            $method,
            $parameters,
            $cookies,
            $files,
            $server,
            $content
        );
        if (count($headers))
            $tokenRequest->headers->add($headers);;
        return app()->handle($tokenRequest);
    }
}
