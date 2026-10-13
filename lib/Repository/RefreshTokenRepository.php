<?php
namespace Beeralex\Oauth2\Repository;

use Beeralex\Core\Repository\Repository;
use Beeralex\Oauth2\Entity\RefreshTokenEntity;
use Beeralex\Oauth2\Tables\RefreshTokensTable;
use Bitrix\Main\Type\DateTime;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

class RefreshTokenRepository extends Repository implements RefreshTokenRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(RefreshTokensTable::class);
    }

    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity): void
    {
        $this->add([
            'ID' => $refreshTokenEntity->getIdentifier(),
            'ACCESS_TOKEN_ID' => $refreshTokenEntity->getAccessToken()->getIdentifier(),
            'IS_REVOKED' => false,
            'EXPIRE_DATE' => DateTime::createFromTimestamp(
                $refreshTokenEntity->getExpiryDateTime()->getTimestamp()
            ),
        ]);
    }

    public function revokeRefreshToken($tokenId): void
    {
        $this->update($tokenId, ['IS_REVOKED' => true]);
    }

    public function isRefreshTokenRevoked($tokenId): bool
    {
        $refreshToken = $this->one(['ID' => $tokenId], ['ID', 'IS_REVOKED']);
        return $refreshToken ? (bool)$refreshToken['IS_REVOKED'] : true;
    }

    public function getNewRefreshToken(): RefreshTokenEntity
    {
        return new RefreshTokenEntity();
    }
}
