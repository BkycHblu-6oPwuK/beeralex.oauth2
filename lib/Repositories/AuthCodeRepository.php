<?php
namespace Beeralex\Oauth2\Repository;

use Beeralex\Core\Repository\Repository;
use Beeralex\Oauth2\Entity\AuthCodeEntity;
use Beeralex\Oauth2\Tables\AuthCodesTable;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

class AuthCodeRepository extends Repository implements AuthCodeRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(AuthCodesTable::class);
    }

    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity): void
    {
        $this->add([
            'ID' => $authCodeEntity->getIdentifier(),
            'IS_REVOKED' => false,
            'USER_ID' => (int)$authCodeEntity->getUserIdentifier(),
            'CLIENT_ID' => $authCodeEntity->getClient()->getIdentifier(),
            'SCOPES' => array_map(
                static fn(ScopeEntityInterface $scopeEntity): string => $scopeEntity->getIdentifier(),
                $authCodeEntity->getScopes()
            ),
        ]);
    }

    public function revokeAuthCode($codeId): void
    {
        $this->update($codeId, ['IS_REVOKED' => true]);
    }

    public function isAuthCodeRevoked($codeId): bool
    {
        $authCode = $this->one(['ID' => $codeId], ['ID', 'IS_REVOKED']);
        return $authCode ? (bool)$authCode['IS_REVOKED'] : true;
    }

    public function getNewAuthCode(): AuthCodeEntity
    {
        return new AuthCodeEntity();
    }
}
