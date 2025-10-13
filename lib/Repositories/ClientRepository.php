<?php
namespace Beeralex\Oauth2\Repository;

use Beeralex\Core\Repository\Repository;
use Beeralex\Oauth2\Entity\ClientEntity;
use Beeralex\Oauth2\Tables\ClientsTable;
use Bitrix\Main\Security\Password;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class ClientRepository extends Repository implements ClientRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(ClientsTable::class);
    }

    public function getClientEntity($clientIdentifier): ?ClientEntity
    {
        $client = $this->query()
            ->setSelect(['*'])
            ->setFilter(['ID' => $clientIdentifier])
            ->exec()
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
        $client = $this->query()
            ->enablePrivateFields()
            ->setSelect(['*', 'SECRET'])
            ->setFilter(['ID' => $clientIdentifier])
            ->exec()
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

        return in_array($grantType, $allowedGrants, true);
    }
}
