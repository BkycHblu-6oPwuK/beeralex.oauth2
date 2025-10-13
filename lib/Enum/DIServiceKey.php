<?php
namespace Beeralex\Oauth2\Enum;

enum DIServiceKey: string
{
    case CLIENT_CREDENTIALS_ACCESS_TOKEN_TTL = 'beeralex.oauth2.grant.client_credentials.access_token_ttl';
    case GRANT_PASSWORD_ACCESS_TOKEN_TTL = 'beeralex.oauth2.grant.password.access_token_ttl';
    case GRANT_AUTH_CODE_TTL = 'beeralex.oauth2.grant.auth_code.ttl';
    case GRANT_AUTH_CODE_ACCESS_TOKEN_TTL = 'beeralex.oauth2.grant.auth_code.access_token_ttl';
    case GRANT_REFRESH_ACCESS_TOKEN_TTL = 'beeralex.oauth2.grant.refresh_token.access_token_ttl';
    case PRIVATE_KEY = 'beeralex.oauth2.private_key';
    case PUBLIC_KEY = 'beeralex.oauth2.public_key';
}