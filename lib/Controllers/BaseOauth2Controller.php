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
    /** @var HttpFactory */
    protected $httpFactory;

    public function __construct(?HttpRequest $request = null)
    {
        parent::__construct($request);
        $this->server = service(AuthorizationServer::class);
        $this->httpFactory = service(HttpFactory::class); 
    }

    protected function processBeforeAction(Action $action): bool
    {
        $action->setArguments([
            'request' => $this->httpFactory->fromBitrixRequest($action->getController()->getRequest()),
            'response' =>  $this->httpFactory->fromBitrixResponse((new HttpResponse())->setStatus(200)),
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
        return $this->httpFactory->toBitrixResponse($result);
    }
}
