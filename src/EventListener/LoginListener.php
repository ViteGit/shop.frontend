<?php

namespace App\EventListener;

use App\Service\CartService;
use App\Service\SecurityService;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener
{
    /**
     * @var SecurityService
     */
    private $securityService;

    /**
     * @var CartService
     */
    private $cartService;

    /**
     * @param SecurityService $securityService
     * @param CartService $cartService
     */
    public function __construct(SecurityService $securityService, CartService $cartService)
    {
        $this->securityService = $securityService;
        $this->cartService = $cartService;
    }

    /**
     * @param InteractiveLoginEvent $event
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();

        $this->securityService->updateLastLogin($user);

        $cart = $this->cartService->getCart();

        if (!empty($cart)) {
            if (empty($cart->getUser())) {
                $this->cartService->updateUser($cart, $user);
            }
        }
    }
}
