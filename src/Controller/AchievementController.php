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

    /**
     * Get all (unlocked / locked) achievements of current user 
     *
     * @api GET
     *
     * @return JsonResponse
     */
    #[Route("/", name:"list", methods: ["GET"])]
    public function getAchievements(Request $request): JsonResponse
    {
        if (!$user = $this->security->getUser()) {
            return new JsonResponse(["message" => "There is no current user."], Response::HTTP_BAD_REQUEST);
        }

        $unlockedAchievements = $this->achievementService->getUnlockedAchievements($user);
        $achievementsToUnlocked = $this->achievementService->getAchievementsToUnlock($user);
        
        // Merge data
        $data = array_merge(
            ["unlocked" => $unlockedAchievements],
            ["locked" => $achievementsToUnlocked]
        );

        $json = $this->serializer->serialize($data, "json", ["groups" => "getAchievements"]);

        // DEBUG !!!
        $this->achievementService->checkToUnlockAchievements($user, $achievementsToUnlocked);
        // DEBUG !!!
        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }
}
