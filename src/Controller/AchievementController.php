<?php

namespace App\Controller;

use App\Repository\AchievementRepository;
use App\Service\AchievementService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route("/api/achievements", name: "app_achievements_")]
#[IsGranted("ROLE_USER")]
class AchievementController extends AbstractController
{
    private AchievementRepository $achievementRep;
    private AchievementService $achievementService;
    private Security $security;
    private SerializerInterface $serializer;

    public function __construct(
        AchievementRepository $achievementRep,
        AchievementService $achievementService,
        Security $security,
        SerializerInterface $serializer
    ) {
        $this->achievementRep = $achievementRep;
        $this->achievementService = $achievementService;
        $this->security = $security;
        $this->serializer = $serializer;
    }
// DEBUG !!!
    /**
     * WIP !!!!!!!!!!! 
     *
     * @api GET
     *
     * @return JsonResponse
     */
    #[Route("/", name:"test", methods: ["GET"])]
    public function getAchievements(Request $request): JsonResponse
    {
        if (!$user = $this->security->getUser()) {
            return new JsonResponse(["message" => "There is no current user."], Response::HTTP_BAD_REQUEST);
        }

        $this->achievementService->hasAchievementToUnlock("social", $user);

        // return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
        return new JsonResponse(null, Response::HTTP_OK, ["accept" => "json"], true);
    }
// DEBUG !!!
}
