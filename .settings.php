<?php
require_once __DIR__ . '/lib/Enum/DIServiceKey.php';

use Beeralex\Oauth2\Enum\DIServiceKey;
use Bitrix\Main\Config\Configuration;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

return [
    'controllers' => [
		'value' => [
			'defaultNamespace' => '\\Beeralex\\OAuth2\\Controllers',
		],
		'readonly' => true,
	],
    'services' => [
        'value' => [
            AccessTokenRepositoryInterface::class => [
                'className' => Beeralex\Oauth2\Repository\AccessTokenRepository::class,
            ],
            AuthCodeRepositoryInterface::class => [
                'className' => Beeralex\Oauth2\Repository\AuthCodeRepository::class,
            ],
            ClientRepositoryInterface::class => [
                'className' => Beeralex\Oauth2\Repository\ClientRepository::class,
            ],
            RefreshTokenRepositoryInterface::class => [
                'className' => Beeralex\Oauth2\Repository\RefreshTokenRepository::class,
            ],
            ScopeRepositoryInterface::class => [
                'className' => Beeralex\Oauth2\Repository\ScopeRepository::class,
            ],
            UserRepositoryInterface::class => [
                'className' => Beeralex\Oauth2\Repository\UserRepository::class,
            ],
            ClientCredentialsGrant::class => [
                'className' => ClientCredentialsGrant::class,
            ],
            DIServiceKey::CLIENT_CREDENTIALS_ACCESS_TOKEN_TTL->value => [
                'className' => DateInterval::class,
                'constructorParams' => ['PT1H'],
            ],
            PasswordGrant::class => [
                'constructor' => static function () {
                    return new PasswordGrant(
                        userRepository: service(UserRepositoryInterface::class),
                        refreshTokenRepository: service(RefreshTokenRepositoryInterface::class),
                    );
                },
            ],
            DIServiceKey::GRANT_PASSWORD_ACCESS_TOKEN_TTL->value => [
                'className' => DateInterval::class,
                'constructorParams' => ['PT1H'],
            ],
            AuthCodeGrant::class => [
                'constructor' => static function () {
                    return new AuthCodeGrant(
                        authCodeRepository: service(AuthCodeRepositoryInterface::class),
                        refreshTokenRepository: service(RefreshTokenRepositoryInterface::class),
                        authCodeTTL: service(DIServiceKey::GRANT_AUTH_CODE_TTL->value),
                    );
                },
            ],
            DIServiceKey::GRANT_AUTH_CODE_TTL->value => [
                'className' => DateInterval::class,
                'constructorParams' => ['PT10M'],
            ],
            DIServiceKey::GRANT_AUTH_CODE_ACCESS_TOKEN_TTL->value => [
                'className' => DateInterval::class,
                'constructorParams' => ['PT1H'],
            ],
            RefreshTokenGrant::class => [
                'constructor' => static function () {
                    return new RefreshTokenGrant(
                        refreshTokenRepository: service(RefreshTokenRepositoryInterface::class),
                    );
                },
            ],
            DIServiceKey::GRANT_REFRESH_ACCESS_TOKEN_TTL->value => [
                'className' => DateInterval::class,
                'constructorParams' => ['PT1H'],
            ],
            DIServiceKey::PRIVATE_KEY->value => [
                'constructor' => static function () {
                    $configuration = Configuration::getValue('beeralex.oauth2');
                    return new CryptKey(
                        keyPath: $configuration['private_key'],
                        passPhrase: $configuration['private_key_passphrase']
                    );
                }
            ],
            DIServiceKey::PUBLIC_KEY->value => [
                'constructor' => static function () {
                    $configuration = Configuration::getValue('beeralex.oauth2');
                    return new CryptKey(
                        keyPath: $configuration['public_key']
                    );
                }
            ],
            AuthorizationServer::class => [
                'constructor' => function (): AuthorizationServer {
                    $configuration = Configuration::getValue('beeralex.oauth2');

                    $server = new AuthorizationServer(
                        clientRepository: service(ClientRepositoryInterface::class),
                        accessTokenRepository: service(AccessTokenRepositoryInterface::class),
                        scopeRepository: service(ScopeRepositoryInterface::class),
                        privateKey: service(DIServiceKey::PRIVATE_KEY->value),
                        encryptionKey: $configuration['encryption_key']
                    );

                    $server->enableGrantType(
                        grantType: service(ClientCredentialsGrant::class),
                        accessTokenTTL: service(DIServiceKey::CLIENT_CREDENTIALS_ACCESS_TOKEN_TTL->value)
                    );

                    $server->enableGrantType(
                        grantType: service(PasswordGrant::class),
                        accessTokenTTL: service(DIServiceKey::GRANT_PASSWORD_ACCESS_TOKEN_TTL->value)
                    );

                    $server->enableGrantType(
                        grantType: service(RefreshTokenGrant::class),
                        accessTokenTTL: service(DIServiceKey::GRANT_REFRESH_ACCESS_TOKEN_TTL->value)
                    );

                    $server->enableGrantType(
                        grantType: service(AuthCodeGrant::class),
                        accessTokenTTL: service(DIServiceKey::GRANT_AUTH_CODE_ACCESS_TOKEN_TTL->value)
                    );

                    return $server;
                },
            ],
        ],
    ],
];
