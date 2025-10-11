<?php

namespace Beeralex\Oauth2\Controllers;

use Beeralex\Core\Http\HttpFactory;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\HttpResponse;
use Bitrix\Main\HttpRequest;
use League\OAuth2\Server\AuthorizationServer;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

abstract class BaseOauth2Controller extends Controller
{
    /** @var AuthorizationServer */
    protected $server;

    /** @var ContainerInterface */
    protected $container;

    public function __construct(?HttpRequest $request = null)
    {
        parent::__construct($request);
        $this->container = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $this->server = $this->container->get(AuthorizationServer::class);
    }

    protected function processBeforeAction(Action $action): bool
    {
        $action->setArguments([
            'request' => HttpFactory::fromBitrixRequest($action->getController()->getRequest()), // request psr
            'response' =>  HttpFactory::fromBitrixResponse((new HttpResponse())->setStatus(200)), // response psr
        ]);
        return true;
    }

    protected function processAfterAction(Action $action, $result): HttpResponse
    {
        if (!$result instanceof ResponseInterface) {
            throw new \RuntimeException(
                'Action result must be instance of "Psr\Http\Message\ResponseInterface"'
            );
        }
        return HttpFactory::toBitrixResponse($result);
    }
}
