<?php

namespace App\Services;

use App\Exceptions\ApiProblemException;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class HttpService
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * HttpService constructor.
     */
    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * Get запрос
     *
     * @param string $url
     * @return mixed
     * @throws
     */
    public function get(string $url)
    {
        try {
            $response = $this->client->request(
                'GET', $url, [
                    'connect_timeout' => 30,
                    'read_timeout' => 30,
                    'timeout' => 30
                ]
            );
        } catch (RequestException $exception){

            throw new ApiProblemException($exception->getMessage(), $exception->getCode());

            //Log::error('HttpService get() ERROR: ' . $exception->getMessage());
            //throw new ApiProblemException('Ошибка HttpService::get()', 500);
        }
        $result = $response->getBody()->getContents();
        $result = json_decode($result);

        if ($result->success === false){
            $errorCodes = 'error-codes';
            if (is_string($result->$errorCodes))
                $error = $result->$errorCodes;

            if (is_array($result->$errorCodes))
                $error = implode(', ', $result->$errorCodes);

            !isset($error) ?  : Log::error('HttpService get() ERROR: ' . $error);

            throw new ApiProblemException("Ваши действия похожи на спам", 403);
        }

        return $result;
    }

}
