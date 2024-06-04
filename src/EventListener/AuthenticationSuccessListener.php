<?php

namespace App\EventListener;

use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;

class AuthenticationSuccessListener
{
    private UserRepository $userRep;

    public function __construct(UserRepository $userRep)
    {
        $this->userRep = $userRep;
    }

    /**
     * @param AuthenticationSuccessEvent $event
     *
     * @return void
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $this->userRep->findOneBy(["email" => $event->getUser()->getUserIdentifier()]);

        if ($user && $user->isValidated() === false) {
            $data["token"] = null;
            $data["message"] = "You don't have validate your email.";
        }

        $event->setData($data);
    }
}
