<?php
$aMenu = [
    [
        "parent_menu" => "global_menu_services",
        "section" => "oauth2",
        "sort" => 200,
        "text" => "OAuth2",
        "title" => "OAuth2 клиенты",
        "icon" => "clouds_menu_icon",
        "page_icon" => "clouds_menu_icon",
        "items_id" => "menu_oauth2",
        "items" => [
            [
                "text" => "Клиенты",
                "url" => "/bitrix/admin/beeralex_oauth2_clients.php",
                "more_url" => ["beeralex_oauth2_client.php"],
            ],
        ],
    ],
];
return $aMenu;
