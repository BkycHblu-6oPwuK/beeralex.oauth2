<?php

use Beeralex\Core\Helpers\FilesHelper;
use Beeralex\Oauth2\Tables\AuthCodesTable;
use Beeralex\Oauth2\Tables\ClientsTable;
use Beeralex\Oauth2\Tables\RefreshTokensTable;
use Beeralex\Oauth2\Tables\TokensTable;
use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

class beeralex_oauth2 extends CModule
{
    public function __construct()
    {

        $arModuleVersion = [];
        include __DIR__ . '/version.php';

        $this->MODULE_ID = 'beeralex.oauth2';
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = 'OAuth2 Server';
        $this->MODULE_DESCRIPTION = 'league oauth2 server';
        $this->PARTNER_NAME = 'Beeralex';
        $this->PARTNER_URI = '#';
    }

    public function DoInstall(): void
    {
        /** @var CMain $APPLICATION */
        global $APPLICATION;

        if ($this->checkRequirements()) {
            ModuleManager::registerModule($this->MODULE_ID);
            Loader::includeModule($this->MODULE_ID);

            $this->InstallDB();
            $this->InstallEvents();
            $this->InstallFiles();
            $this->InstallTasks();
        } else {
            $APPLICATION->ThrowException('Ошибка установки. Проверьте зависимости модуля');
        }

        $APPLICATION->IncludeAdminFile(
            'Установка модуля',
            $this->getPath() . '/install/step.php'
        );
    }

    public function InstallFiles()
    {
        $moduleDir = __DIR__;
        $sourceDir = $moduleDir . '/files';
        $targetDir = Application::getDocumentRoot();

        FilesHelper::copyRecursive($sourceDir, $targetDir);
    }

    public function DoUninstall(): void
    {
        /** @var CMain $APPLICATION */
        global $APPLICATION;

        $request = Context::getCurrent()->getRequest();

        if ((int)$request->get('step') <= 1) {
            $APPLICATION->IncludeAdminFile(
                'Удаление модуля',
                $this->getPath() . '/install/unstep.php'
            );
        }

        if ((int)$request->get('step') === 2) {
            Loader::includeModule($this->MODULE_ID);
            $this->UnInstallDB();
            $this->UnInstallEvents();
            $this->UnInstallFiles();
            $this->UnInstallTasks();

            Loader::clearModuleCache($this->MODULE_ID);
            ModuleManager::unRegisterModule($this->MODULE_ID);
        }
    }

    public function InstallDB(): void
    {
        AuthCodesTable::createTable();
        ClientsTable::createTable();
        RefreshTokensTable::createTable();
        TokensTable::createTable();
    }

    public function UnInstallDB(): void
    {
        AuthCodesTable::dropTable();
        ClientsTable::dropTable();
        RefreshTokensTable::dropTable();
        TokensTable::dropTable();
    }

    public function checkRequirements(): bool
    {
        return version_compare(ModuleManager::getVersion('main'), '23.00.00') >= 0;
    }

    public function getPath(bool $includeDocumentRoot = true): string
    {
        return $includeDocumentRoot
            ? dirname(__DIR__)
            : (string)str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__));
    }
}
