<?php
/**
 * Created by PhpStorm.
 * User: odiahv
 * Date: 14/02/2018
 * Time: 10:11
 */

namespace MauticPlugin\GRGCSRFBundle\Listeners\Http;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class AjaxCSRFSubscriber implements EventSubscriberInterface
{
    /**
     * @var CsrfTokenManagerInterface
     */
    protected $provider;

    protected $env;

    /**
     * @param CsrfTokenManagerInterface $provider
     * @param string $env environment name
     */
    public function __construct(CsrfTokenManagerInterface $provider, $env = "prod")
    {
        $this->provider = $provider;
        $this->env = $env;
    }

    /**
     * @param GetResponseEvent $e
     */
    public function onKernelRequest(GetResponseEvent $e)
    {
        $request = $e->getRequest();
        if (in_array($request->getMethod(), ['POST', 'PUT', 'DELETE', 'PATCH']) && $request->isXmlHttpRequest()) {
            $token = $request->request->get('csrf_token');
            $tokenId = $request->request->get('csrf_token_id');
            if (! $token) {
                list($tokenId, $token) = $this->getTokenFromRequest($request->request->all());
            }
            if (! $token || ! $tokenId) {
                $e->setResponse($this->sendJsonResponse(['success' => 0, 'message' => 'No CSRF token provided'], 200));
                return;
            }
            if (!$this->isCsrfTokenValid($tokenId, $token)) {
                $e->setResponse($this->sendJsonResponse(['success' => 0, 'message' => 'The CSRF token is invalid'], 200));
                return;
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            'kernel.request' => array('onKernelRequest', 1000),
        );
    }

    /**
     * @param $dataArray
     * @param null $statusCode
     * @param bool|true $addIgnoreWdt
     * @return JsonResponse
     * @throws \Exception
     */
    protected function sendJsonResponse($dataArray, $statusCode = null, $addIgnoreWdt = true)
    {
        $response = new JsonResponse();

        if ($this->env == 'dev' && $addIgnoreWdt) {
            $dataArray['ignore_wdt'] = 1;
        }
        if ($statusCode !== null) {
            $response->setStatusCode($statusCode);
        }
        $response->setData($dataArray);

        return $response;
    }

    protected function isCsrfTokenValid($id, $token)
    {
        return $this->provider->isTokenValid(new CsrfToken($id, $token));
    }

    protected function getTokenFromRequest($requestData = [])
    {
        $keys = array_keys($requestData);
        if (count($keys) > 0) {
            $keyName = $keys[0];
            $data = $requestData[$keyName];
            if (is_array($data) && array_key_exists("_token", $data)) {
                $token = $data['_token'];
                return [$keyName, $token];
            }
        }
    }


}