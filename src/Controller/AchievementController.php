<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\AchievementService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route("/api/achievements", name: "app_achievements_")]
#[IsGranted("ROLE_USER")]
class AchievementController extends AbstractController
{
    private UserRepository $userRep;
    private AchievementService $achievementService;
    private Security $security;
    private SerializerInterface $serializer;

    public function __construct(
        UserRepository $userRep,
        AchievementService $achievementService,
        Security $security,
        SerializerInterface $serializer
    ) {
        $this->userRep = $userRep;
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
    public function getAchievements(): JsonResponse
    {
        if (!$user = $this->security->getUser()) {
            return new JsonResponse(["message" => "There is no current user."], Response::HTTP_BAD_REQUEST);
        }
        if (null === $user = $this->userRep->findOneBy(["email" => $user->getUserIdentifier()])) {
            return new JsonResponse(["message" => "Current user not found."], Response::HTTP_BAD_REQUEST);
        }

        $unlockedAchievements = $this->achievementService->getUnlockedAchievements($user);
        $achievementsToUnlocked = $this->achievementService->getAchievementsToUnlock($user);

        // Merge data
        $data = array_merge(
            ["unlocked" => $unlockedAchievements],
            ["locked" => $achievementsToUnlocked]
        );

        $json = $this->serializer->serialize($data, "json", ["groups" => "getAchievements"]);

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }
}
