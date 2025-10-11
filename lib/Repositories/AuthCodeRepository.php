<?php

namespace Beeralex\Oauth2\Repositories;

use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use Beeralex\Oauth2\Entity\AuthCodeEntity;
use Beeralex\Oauth2\Tables\AuthCodesTable;

class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity): void
    {
        AuthCodesTable::add([
            'ID' => $authCodeEntity->getIdentifier(),
            'IS_REVOKED' => false,
            'USER_ID' => (int)$authCodeEntity->getUserIdentifier(),
            'CLIENT_ID' => $authCodeEntity->getClient()->getIdentifier(),
            'SCOPES' => array_map(function (ScopeEntityInterface $scopeEntity): string {
                return $scopeEntity->getIdentifier();
            }, $authCodeEntity->getScopes()),
        ]);
    }

    public function revokeAuthCode($codeId): void
    {
        AuthCodesTable::update($codeId, ['IS_REVOKED' => true]);
    }

    public function isAuthCodeRevoked($codeId): bool
    {
        $authCode = AuthCodesTable::query()
            ->addSelect('ID')
            ->addSelect('IS_REVOKED')
            ->where('ID', $codeId)
            ->fetchObject();

        if ($authCode !== null) {
            return $authCode->getIsRevoked();
        }

        return true;
    }

    public function getNewAuthCode(): AuthCodeEntity
    {
        return new AuthCodeEntity();
    }
}
