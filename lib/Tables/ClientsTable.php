<?php

namespace Beeralex\Oauth2\Tables;

use Beeralex\Core\Traits\TableManagerTrait;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\EnumField;
use Bitrix\Main\ORM\Fields\StringField;

class ClientsTable extends DataManager
{
    use TableManagerTrait;

    public static function getTableName(): string
    {
        return 'beeralex_oauth2_clients';
    }

    public static function getMap(): array
    {
        return [
            (new StringField('ID'))
                ->configurePrimary(true)
                ->configureSize(80),

            (new StringField('NAME'))
                ->configureSize(100),

            (new BooleanField('IS_CONFIDENTIAL')),

            (new StringField('REDIRECT_URI'))
                ->configureRequired(true)
                ->configureSize(255),

            (new ArrayField('GRANT_TYPES'))
                ->configureRequired(true)
                ->configureDefaultValue([])
                ->configureSerializationJson(),

            (new StringField('SECRET'))
                ->configureSize(255)
                ->configurePrivate(true),
        ];
    }
}
