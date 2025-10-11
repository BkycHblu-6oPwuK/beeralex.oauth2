<?php

namespace Beeralex\Oauth2\Service;

use Bitrix\Main\HttpResponse;
use Bitrix\Main\Web\HttpHeaders;
use Psr\Http\Message\ResponseInterface;

class Response
{
    private ?HttpResponse $response = null;
    private ?ResponseInterface $psrResponse = null;

    private function __construct(){}

    public static function createInstanceFromBitrixResponse(HttpResponse $response)
    {
        $instance = new self();
        $instance->response = $response;
        $instance->createPsrResponceFromBitrix($response);
        return $instance;
    }

    public static function createInstanceFromPsrResponse(ResponseInterface $response)
    {
        $instance = new self();
        $instance->psrResponse = $response;
        $instance->createBitrixResponseFromPsr($response);
        return $instance;
    }

    public function getBitrixResponse() : HttpResponse
    {
        return $this->response;
    }

    public function getPsrResponse(): ResponseInterface
    {
        return $this->psrResponse;
    }

    public function createPsrResponceFromBitrix(HttpResponse $response)
    {
        $serverProtocol = \Bitrix\Main\Context::getCurrent()->getServer()->get('SERVER_PROTOCOL');
        $statusCode = 200;
        $prefixStatus = strtolower($serverProtocol . ' ');
        $prefixStatusLength = strlen($prefixStatus);
        if ($this->response->getStatus() !== null) {
            $statusCode = (int)substr($response->getStatus(), $prefixStatusLength);
        }
        $statusCode = 200;
        $this->psrResponse = new \GuzzleHttp\Psr7\Response(
            $statusCode,
            array_filter(
                Http::collectHttpHeaders($response->getHeaders()),
                function (string $name) use ($response): bool {
                    return $response->getStatus() === null || $name !== $response->getStatus();
                },
                ARRAY_FILTER_USE_KEY
            ),
            $response->getContent(),
            Http::parseHttpProtocolVersion($serverProtocol)
        );
    }

    public function createBitrixResponseFromPsr(ResponseInterface $response)
    {
        $headers = new HttpHeaders();
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $headers->add($name, $value);
            }
        }

        $this->response = (new HttpResponse())
        ->setStatus($response->getStatusCode())
        ->setHeaders($headers)
        ->setContent((string)$response->getBody());
    }
}
