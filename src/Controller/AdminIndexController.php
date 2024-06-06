<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_ADMIN")]
class AdminIndexController extends AbstractController
{
    /**
     * Admin Homepage
     *
     * @return Response
     */
    #[Route("/", name:"homepage")]
    public function homepage(): Response
    {
        return $this->render("index.html.twig");
    }
}
