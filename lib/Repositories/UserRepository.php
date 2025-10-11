<?php

namespace Beeralex\Oauth2\Repositories;

use Bitrix\Main\Security\Password;
use Bitrix\Main\UserTable;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Beeralex\Oauth2\Entity\UserEntity;

class UserRepository implements UserRepositoryInterface
{
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ): ?UserEntity {
        $user = UserTable::query()
            ->enablePrivateFields()
            ->addSelect('ID')
            ->addSelect('PASSWORD')
            ->where('ACTIVE', true)
            ->where('BLOCKED', false)
            ->where('LOGIN', $username)
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
