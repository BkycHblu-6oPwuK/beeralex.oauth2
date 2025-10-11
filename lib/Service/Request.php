<?php
namespace Beeralex\Oauth2\Service;
use Bitrix\Main\HttpRequest;
use League\OAuth2\Server\CryptKeyInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Psr\Http\Message\ServerRequestInterface;
use Beeralex\Oauth2\Repositories\AccessTokenRepository;

class Request
{
    private ?HttpRequest $request = null;
    private ?ServerRequestInterface $psrRequest = null;
    public function __construct(HttpRequest $request)
    {
        $this->request = $request;
    }

    public function tokenIsValid(CryptKeyInterface $cryptKeyObject)
    {
        $AccessTokenRepository = new AccessTokenRepository();
        $psrRequest = $this->psrRequest ?? $this->getPsrServerRequest();
        try {
            (new ResourceServer($AccessTokenRepository, $cryptKeyObject))->validateAuthenticatedRequest($psrRequest);
            return true;
        } catch (OAuthServerException $e) {
            return $e->getMessage();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return false;
    }

    public function getBitrixRequest()
    {
        return $this->request;
    }

    public function getPsrServerRequest(): ServerRequestInterface
    {
        if($this->psrRequest === null){
            $serverRequest = new \GuzzleHttp\Psr7\ServerRequest(
                $this->request->getRequestMethod(),
                $this->request->getRequestUri(),
                Http::collectHttpHeaders($this->request->getHeaders()),
                HttpRequest::getInput(),
                Http::parseHttpProtocolVersion($this->request->getServer()->get('SERVER_PROTOCOL')),
                $this->request->getServer()->toArray()
            );
    
            $serverRequest = $serverRequest
                ->withCookieParams($this->request->getCookieList()->getValues())
                ->withQueryParams($this->request->getQueryList()->getValues())
                ->withParsedBody($this->request->getPostList()->getValues())
                ->withUploadedFiles(\GuzzleHttp\Psr7\ServerRequest::normalizeFiles(
                    $this->request->getFileList()->getValues()
                ));
            $this->psrRequest = $serverRequest;
        }

        return $this->psrRequest;
    }
}