<?php
require_once __DIR__ . '/lib/Enum/DIServiceKey.php';

use Beeralex\Oauth2\Enum\DIServiceKey;
use Bitrix\Main\Config\Configuration;
use Bitrix\Main\DI\ServiceLocator;
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
                'className' => PasswordGrant::class,
                'constructorParams' => function (): array {
                    $container = Bitrix\Main\DI\ServiceLocator::getInstance();
                    return [
                        $container->get(UserRepositoryInterface::class),
                        $container->get(RefreshTokenRepositoryInterface::class),
                    ];
                },
            ],
            DIServiceKey::GRANT_PASSWORD_ACCESS_TOKEN_TTL->value => [
                'className' => DateInterval::class,
                'constructorParams' => ['PT1H'],
            ],
            AuthCodeGrant::class => [
                'className' => AuthCodeGrant::class,
                'constructorParams' => function (): array {
                    $container = ServiceLocator::getInstance();
                    return [
                        $container->get(AuthCodeRepositoryInterface::class),
                        $container->get(RefreshTokenRepositoryInterface::class),
                        $container->get(DIServiceKey::GRANT_AUTH_CODE_TTL->value),
                    ];
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
                'className' => RefreshTokenGrant::class,
                'constructorParams' => function (): array {
                    $container = ServiceLocator::getInstance();
                    return [
                        $container->get(RefreshTokenRepositoryInterface::class),
                    ];
                },
            ],
            DIServiceKey::GRANT_REFRESH_ACCESS_TOKEN_TTL->value => [
                'className' => DateInterval::class,
                'constructorParams' => ['PT1H'],
            ],
            DIServiceKey::PRIVATE_KEY->value => [
                'className' => CryptKey::class,
                'constructorParams' => function (): array {
                    $configuration = Configuration::getValue('beeralex.oauth2');
                    return [
                        $configuration['private_key'],
                        $configuration['private_key_passphrase'],
                    ];
                },
            ],
            DIServiceKey::PUBLIC_KEY->value => [
                'className' => CryptKey::class,
                'constructorParams' => function (): array {
                    $configuration = Configuration::getValue('beeralex.oauth2');
                    return [
                        $configuration['public_key'],
                    ];
                },
            ],
            AuthorizationServer::class => [
                'constructor' => function (): AuthorizationServer {
                    $container = ServiceLocator::getInstance();
                    $configuration = Configuration::getValue('beeralex.oauth2');

                    $server = new AuthorizationServer(
                        $container->get(ClientRepositoryInterface::class),
                        $container->get(AccessTokenRepositoryInterface::class),
                        $container->get(ScopeRepositoryInterface::class),
                        $container->get(DIServiceKey::PRIVATE_KEY->value),
                        $configuration['encryption_key']
                    );

                    $server->enableGrantType(
                        $container->get(ClientCredentialsGrant::class),
                        $container->get(DIServiceKey::CLIENT_CREDENTIALS_ACCESS_TOKEN_TTL->value)
                    );

                    $server->enableGrantType(
                        $container->get(PasswordGrant::class),
                        $container->get(DIServiceKey::GRANT_PASSWORD_ACCESS_TOKEN_TTL->value)
                    );

                    $server->enableGrantType(
                        $container->get(RefreshTokenGrant::class),
                        $container->get(DIServiceKey::GRANT_REFRESH_ACCESS_TOKEN_TTL->value)
                    );

                    $server->enableGrantType(
                        $container->get(AuthCodeGrant::class),
                        $container->get(DIServiceKey::GRANT_AUTH_CODE_ACCESS_TOKEN_TTL->value)
                    );

                    return $server;
                },
            ],
        ],
    ],
];
