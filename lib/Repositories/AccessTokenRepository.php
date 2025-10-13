<?php
namespace Beeralex\Oauth2\Repository;

use Beeralex\Core\Repository\Repository;
use Beeralex\Oauth2\Entity\AccessTokenEntity;
use Beeralex\Oauth2\Tables\TokensTable;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

class AccessTokenRepository extends Repository implements AccessTokenRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(TokensTable::class);
    }

    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
        $this->add([
            'ID' => $accessTokenEntity->getIdentifier(),
            'IS_REVOKED' => false,
            'USER_ID' => (int)$accessTokenEntity->getUserIdentifier(),
            'CLIENT_ID' => $accessTokenEntity->getClient()->getIdentifier(),
            'SCOPES' => array_map(
                static fn(ScopeEntityInterface $scopeEntity): string => $scopeEntity->getIdentifier(),
                $accessTokenEntity->getScopes()
            ),
        ]);
    }

    public function revokeAccessToken($tokenId): void
    {
        $this->update($tokenId, ['IS_REVOKED' => true]);
    }

    public function isAccessTokenRevoked($tokenId): bool
    {
        $token = $this->one(['ID' => $tokenId], ['ID', 'IS_REVOKED']);
        return $token ? (bool)$token['IS_REVOKED'] : true;
    }

    public function getNewToken(
        ClientEntityInterface $clientEntity,
        array $scopes,
        $userIdentifier = null
    ): AccessTokenEntity {
        $accessToken = new AccessTokenEntity();
        $accessToken->setClient($clientEntity);

        foreach ($scopes as $scope) {
            $accessToken->addScope($scope);
        }

        $accessToken->setUserIdentifier($userIdentifier ?? $clientEntity->getIdentifier());
        return $accessToken;
    }
}

