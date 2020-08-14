<?php

namespace App\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Http\JsonResponse;

trait GuzzleRequestTrait
{
    protected $client;

    public function makeHttpRequest($method, $url, $header = null, $body = null, $body_type = 'form_params')
    {
        $this->client = new Client([
            'headers' => $header,
        ]);

        if($body_type === 'json') {
            $client_bd = [
                'json' => $body,
            ];
        } else {
            $client_bd = [
                'form_params' => $body,
            ];
        }

        $response = null;

        if ($method == 'GET') {
            $response = $this->doGet($url);
        }
        if ($method == 'POST') {
            $response = $this->doPost($url, $client_bd);
        }
        if ($method == 'MULTIPART') {
            $response = $this->doMultiPart($url, $body);
        }
        if ($method == 'DELETE') {
            $response = $this->doDelete($url, $body);
        }
        return $response;
    }

    public function doGet($url)
    {
        return $this->client->request('GET', $url);
    }

    public function doPost($url, $body)
    {
        return $this->client->request('POST', $url, $body);
    }

    public function doMultiPart($url, $body = [])
    {
        return $this->client->request('POST', $url, [
            'multipart' => $body
        ]);
    }

    public function doDelete($url, $body)
    {
        return $this->client->request('DELETE', $url, $body);
    }
}
