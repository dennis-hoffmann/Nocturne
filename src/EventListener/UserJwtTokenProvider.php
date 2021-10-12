<?php

namespace App\EventListener;

use App\Service\Mercure;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class UserJwtTokenProvider
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var Mercure
     */
    private $mercure;

    public function __construct(RequestStack $requestStack, Mercure $mercure)
    {
        $this->requestStack = $requestStack;
        $this->mercure      = $mercure;
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        if ($this->requestStack->getCurrentRequest()->cookies->has('mercureAuthorization')) {
            return;
        };

        $response = $event->getResponse();

        $cookie = new Cookie('mercureAuthorization', $this->mercure->getJwt(), 0, '/', null, false, true, false, 'strict');
        $response->headers->setCookie($cookie);

        $event->setResponse($response);
    }
}