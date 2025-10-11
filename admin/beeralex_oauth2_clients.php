<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

use Beeralex\Oauth2\Tables\ClientsTable;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;

$MODULE_ID = "beeralex.oauth2";
$POST_RIGHT = $APPLICATION->GetGroupRight($MODULE_ID);
if ($POST_RIGHT == "D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

Loader::includeModule($MODULE_ID);

$request = Application::getInstance()->getContext()->getRequest();
$sTableID = "tbl_oauth_clients";
$oSort = new CAdminUiSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminUiList($sTableID, $oSort);

if (($arID = $lAdmin->GroupAction()) && $POST_RIGHT == "W") {
    $type = $request->get("action_button_{$sTableID}");
    foreach ($arID as $id) {
        if ($type == "delete") {
            ClientsTable::delete($id);
        }
    }
}

// --- Фильтры ---
$displayFilter = [
    ["id" => "ID", "name" => "ID", "type" => "string", "default" => true],
    ["id" => "NAME", "name" => "Название", "type" => "string", "default" => true],
    ["id" => "IS_CONFIDENTIAL", "name" => "Тип", "type" => "list", "items" => [
        "Y" => "Конфиденциальный",
        "N" => "Публичный"
    ]],
];
$filter = [];
$lAdmin->AddFilter($displayFilter, $filter);

$nav = $lAdmin->getPageNavigation($sTableID);
$query = ClientsTable::getList([
    'select' => ['ID', 'NAME', 'IS_CONFIDENTIAL', 'REDIRECT_URI'],
    'filter' => $filter,
    'order' => [$oSort->getField() => $oSort->getOrder()],
    'count_total' => true,
    'offset' => $nav->getOffset(),
    'limit' => $nav->getLimit(),
]);
$nav->setRecordCount($query->getCount());
$lAdmin->setNavigation($nav, "", false);
$result = $query->fetchAll();

// --- Заголовки ---
$lAdmin->AddHeaders([
    ['id' => 'ID', 'content' => 'ID', 'sort' => 'ID', 'default' => true],
    ['id' => 'NAME', 'content' => 'Название', 'sort' => 'NAME', 'default' => true],
    ['id' => 'IS_CONFIDENTIAL', 'content' => 'Тип клиента', 'sort' => 'IS_CONFIDENTIAL', 'default' => true],
    ['id' => 'REDIRECT_URI', 'content' => 'Redirect URI', 'sort' => 'REDIRECT_URI', 'default' => true],
]);

foreach ($result as $item) {
    $row = &$lAdmin->AddRow($item['ID'], $item, "/bitrix/admin/beeralex_oauth2_client.php?ID=" . $item['ID'], "Редактировать");
    $row->AddField('ID', $item['ID']);
    $row->AddField('NAME', htmlspecialcharsbx($item['NAME']));
    $row->AddField('IS_CONFIDENTIAL', $item['IS_CONFIDENTIAL'] ? 'Конфиденциальный' : 'Публичный');
    $row->AddField('REDIRECT_URI', htmlspecialcharsbx($item['REDIRECT_URI']));

    $arActions = [
        ["ICON" => "edit", "TEXT" => "Редактировать", "LINK" => "/bitrix/admin/beeralex_oauth2_client.php?ID=" . $item['ID'], "DEFAULT" => true],
    ];
    $row->AddActions($arActions);
}

if ($request->get("new_secret")) {
    echo '<div class="adm-info-message">Секрет клиента: <b>' . htmlspecialcharsbx($request->get("new_secret")) . '</b><br>Сохраните его — больше он не будет показан.</div>';
}

$lAdmin->AddGroupActionTable([
    "delete" => "Удалить выбранные",
]);

// --- Кнопки над таблицей ---
$aContext = [
    [
        "TEXT" => "Добавить клиента",
        "LINK" => "/bitrix/admin/beeralex_oauth2_client.php?lang=" . LANG,
        "TITLE" => "Создать новый OAuth клиент",
        "ICON" => "btn_new",
    ],
];
$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle("OAuth2 Клиенты");
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayFilter($displayFilter);
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
