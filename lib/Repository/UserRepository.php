<?php
namespace Beeralex\Oauth2\Repository;

use Beeralex\Core\Repository\Repository;
use Beeralex\Oauth2\Entity\UserEntity;
use Bitrix\Main\Security\Password;
use Bitrix\Main\UserTable;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

class UserRepository extends Repository implements UserRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(UserTable::class);
    }

    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ): ?UserEntity {
        $user = $this->query()
            ->enablePrivateFields()
            ->setSelect(['ID', 'PASSWORD'])
            ->setFilter([
                'ACTIVE' => true,
                'BLOCKED' => false,
                'LOGIN' => $username,
            ])
            ->exec()
            ->fetchObject();

        if ($user === null) {
            return null;
        }

        if (!Password::equals($user->getPassword(), $password)) {
            return null;
        }

        $userEntity = new UserEntity();
        $userEntity->setIdentifier((string)$user->getId());

        return $userEntity;
    }
}
