<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

use Beeralex\Oauth2\Repository\ClientRepository;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Security\Random;
use Symfony\Component\Uid\Uuid;

$MODULE_ID = "beeralex.oauth2";
$POST_RIGHT = $APPLICATION->GetGroupRight($MODULE_ID);
if ($POST_RIGHT == "D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

Loader::includeModule($MODULE_ID);

$request = Application::getInstance()->getContext()->getRequest();
$clientId = $request->getQuery("ID");
$clientRepository = new ClientRepository();
$client = null;

if ($clientId) {
    $client = $clientRepository->getById($clientId);
    if (!$client) {
        ShowError("Клиент не найден");
        require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
        exit;
    }
}

if ($request->isPost() && check_bitrix_sessid()) {
    $data = [
        'NAME' => $request->getPost('NAME'),
        'IS_CONFIDENTIAL' => $request->getPost('IS_CONFIDENTIAL') === 'Y',
        'REDIRECT_URI' => $request->getPost('REDIRECT_URI'),
        'GRANT_TYPES' => $request->getPost('GRANT_TYPES') ?: []
    ];
    
    $newSecretPlain = null;

    if (!$client) {
        $data['ID'] = Uuid::v4()->toRfc4122();
        if ($data['IS_CONFIDENTIAL']) {
            $newSecretPlain = Random::getString(48);
            $data['SECRET'] = \Bitrix\Main\Security\Password::hash($newSecretPlain);
        }
        $clientRepository->add($data);
    } else {
        $clientRepository->update($clientId, $data);
    }

    if ($request->getPost("save")) {
        LocalRedirect("/bitrix/admin/beeralex_oauth2_clients.php?new_secret=" . urlencode($newSecretPlain));
    } elseif ($request->getPost("apply")) {
        $id = $client ? $clientId : $data['ID'];
        LocalRedirect("/bitrix/admin/beeralex_oauth2_client.php?ID=" . $id . "&apply=Y&new_secret=" . urlencode($newSecretPlain));
    }
}

$APPLICATION->SetTitle($client ? "Редактирование клиента" : "Создание клиента");
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = [
    [
        "TEXT"  => "Список клиентов",
        "TITLE" => "OAuth2 Клиенты",
        "LINK"  => "beeralex_oauth2_clients.php?lang=" . LANG,
        "ICON"  => "btn_list",
    ],
];
$context = new CAdminContextMenu($aMenu);
$context->Show();

$aTabs = [[
    "DIV" => "edit1",
    "TAB" => "Основные данные",
    "ICON" => "main_user_edit",
    "TITLE" => "Параметры клиента",
]];

if ($request->get("new_secret")) {
    echo '<div class="adm-info-message">Секрет клиента: <b>' . htmlspecialcharsbx($request->get("new_secret")) . '</b><br>Сохраните его — больше он не будет показан.</div>';
}

$formUrl = $APPLICATION->GetCurPage() . ($client ? "?ID=" . $clientId : "");
?>
<form method="POST" action="<?= $formUrl ?>">
    <?php
    $tabControl = new CAdminTabControl("tabControl", $aTabs);
    $tabControl->Begin();
    $tabControl->BeginNextTab();
    echo bitrix_sessid_post();
    ?>

    <?php if ($client): ?>
        <tr>
            <td width="40%">ID:</td>
            <td><?= htmlspecialcharsbx($client['ID']) ?></td>
        </tr>
        <?php if ($client['IS_CONFIDENTIAL'] && $client['SECRET']): ?>
            <tr>
                <td>Секрет:</td>
                <td><input type="text" readonly value="<?= htmlspecialcharsbx($client['SECRET']) ?>" size="60"></td>
            </tr>
        <?php endif; ?>
    <?php endif; ?>

    <tr>
        <td width="40%">Название:</td>
        <td><input type="text" name="NAME" value="<?= htmlspecialcharsbx($client['NAME'] ?? '') ?>" size="60"></td>
    </tr>

    <tr>
        <td>Тип авторизации:</td>
        <td>
            <select name="IS_CONFIDENTIAL">
                <option value="N" <?= empty($client['IS_CONFIDENTIAL']) ? 'selected' : '' ?>>Публичный (Implicit)</option>
                <option value="Y" <?= !empty($client['IS_CONFIDENTIAL']) ? 'selected' : '' ?>>Конфиденциальный (Authorization Code)</option>
            </select>
        </td>
    </tr>

    <tr>
        <td>Redirect URI:</td>
        <td><input type="text" name="REDIRECT_URI" value="<?= htmlspecialcharsbx($client['REDIRECT_URI'] ?? '') ?>" size="60"></td>
    </tr>

    <tr>
        <td>Типы авторизации (grant types):</td>
        <td>
            <?php
            $grantTypes = [
                'authorization_code' => 'Authorization Code (через redirect_uri)',
                'client_credentials' => 'Client Credentials (machine-to-machine)',
                'password' => 'Password (логин/пароль пользователя)',
                'implicit' => 'Implicit (устаревший, без секрета)',
            ];
            $selected = $client['GRANT_TYPES'] ?? [];
            foreach ($grantTypes as $value => $label): ?>
                <label style="display:block;margin-bottom:4px;">
                    <input type="checkbox" name="GRANT_TYPES[]" value="<?= $value ?>"
                        <?= in_array($value, $selected ?? [], true) ? 'checked' : '' ?>>
                    <?= htmlspecialcharsbx($label) ?>
                </label>
            <?php endforeach; ?>
        </td>
    </tr>

    <?php
    $tabControl->End();
    ?>
    <div style="margin-top:15px;">
        <input type="submit" name="save" value="Сохранить" class="adm-btn-save">
        <input type="submit" name="apply" value="Применить">
    </div>
</form>

<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
