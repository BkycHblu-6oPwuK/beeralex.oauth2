<?php

namespace Beeralex\Oauth2\Repositories;

use Bitrix\Main\Security\Password;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use Beeralex\Oauth2\Entity\ClientEntity;
use Beeralex\Oauth2\Tables\ClientsTable;

class ClientRepository implements ClientRepositoryInterface
{
    public function getClientEntity($clientIdentifier): ?ClientEntity
    {
        $client = ClientsTable::query()
            ->addSelect('*')
            ->where('ID', $clientIdentifier)
            ->fetchObject();

        if ($client === null) {
            return null;
        }

        $clientEntity = new ClientEntity();

        $clientEntity->setIdentifier($client->getId());
        $clientEntity->setName($client->getName());
        $clientEntity->setRedirectUri($client->getRedirectUri());
        if ($client->getIsConfidential()) {
            $clientEntity->setConfidential();
        }

        return $clientEntity;
    }

    public function validateClient($clientIdentifier, $clientSecret, $grantType): bool
    {
        $client = ClientsTable::query()
            ->enablePrivateFields()
            ->addSelect('*')
            ->addSelect('SECRET')
            ->where('ID', $clientIdentifier)
            ->fetchObject();

        if ($client === null) {
            return false;
        }

        if ($client->getIsConfidential()) {
            if ($client->getSecret() === null) {
                return false;
            }
            if (!Password::equals($client->getSecret(), $clientSecret)) {
                return false;
            }
        }

        $allowedGrants = $client->getGrantTypes() ?? [];
        if (!is_array($allowedGrants)) {
            $allowedGrants = (array)$allowedGrants;
        }

        if (!in_array($grantType, $allowedGrants, true)) {
            return false;
        }

        return true;
    }
}
