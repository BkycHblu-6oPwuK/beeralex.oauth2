<?php
namespace Beeralex\Oauth2\Service;

use Bitrix\Main\Web\HttpHeaders;

class Http
{
    public static function collectHttpHeaders(HttpHeaders $headers): array
    {
        $list = [];

        /** @var array{name: string, values: string[]} $header */
        foreach ($headers->toArray() as $header) {
            $list[$header['name']] = $header['values'];
        }

        return $list;
    }
    public static function parseHttpProtocolVersion(?string $serverProtocol): string
    {
        return $serverProtocol !== null
            ? str_replace('HTTP/', '', $serverProtocol)
            : '1.0';
    }
}