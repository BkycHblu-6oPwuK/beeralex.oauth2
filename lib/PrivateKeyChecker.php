<?php
namespace Beeralex\Oauth2;

use Bitrix\Main\Config\Configuration;

class PrivateKeyChecker
{
    /**
     * должен быть ~ resource(8) of type (OpenSSL key) и true
     */
    public static function check(): void
    {
        $configuration = Configuration::getValue('beeralex.oauth2');
        $res = \openssl_pkey_get_private("file://{$configuration['private_key']}", null);
        if ($res === false) {
            var_dump(openssl_error_string());
            die();
        }
        var_dump($res); // 
        $details = \openssl_pkey_get_details($res);
        echo '<pre>';
        var_dump($details !== false && \in_array(
            $details['type'] ?? -1,
            [OPENSSL_KEYTYPE_RSA, OPENSSL_KEYTYPE_EC],
            true
        ));
        echo '</pre>';
    }
}
