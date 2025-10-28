<?php
namespace Beeralex\Oauth2\ActionFilter;

use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Context;
use League\OAuth2\Server\ResourceServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use Beeralex\Core\Http\HttpFactory;
use Beeralex\Oauth2\Enum\DIServiceKey;

class Oauth2Token extends Base
{
    public const ERROR_INVALID_TOKEN = 'invalid_token';

    public function onBeforeAction(Event $event)
    {
        $request = Context::getCurrent()->getRequest();
        $psrRequest = HttpFactory::fromBitrixRequest($request);
        $accessTokenRepository = service(AccessTokenRepositoryInterface::class);
        $publicKey = service(DIServiceKey::PUBLIC_KEY->value);
        $resourceServer = new ResourceServer($accessTokenRepository, $publicKey);

        try {
            $validatedRequest = $resourceServer->validateAuthenticatedRequest($psrRequest);

            $userId = $validatedRequest->getAttribute('oauth_user_id');
            if ($userId && is_numeric($userId)) {
                global $USER;
                if ($USER instanceof \CUser && !$USER->IsAuthorized()) {
                    $USER->Authorize((int)$userId);
                }
            }

            return null;
        } catch (OAuthServerException $e) {
            Context::getCurrent()->getResponse()->setStatus(401);
            $this->addError(new Error($e->getMessage(), self::ERROR_INVALID_TOKEN));
            return new EventResult(EventResult::ERROR, null, null, $this);
        } catch (\Exception $e) {
            Context::getCurrent()->getResponse()->setStatus(500);
            $this->addError(new Error('Internal server error: ' . $e->getMessage(), self::ERROR_INVALID_TOKEN));
            return new EventResult(EventResult::ERROR, null, null, $this);
        }
    }
}
