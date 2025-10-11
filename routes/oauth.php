<?php

use Bitrix\Main\Routing\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->post(
        '/oauth/token',
        [Beeralex\Oauth2\Controllers\AccessTokensController::class, 'issueToken']
    );
    $routes->get(
        '/oauth/authorize',
        [Beeralex\Oauth2\Controllers\AuthorizationController::class, 'authorize']
    );
};
