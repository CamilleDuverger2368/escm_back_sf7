<?php

namespace App\Controller\API\Routes;

use App\Entity\Entreprise;
use App\Entity\Escape;
use App\Entity\User;
use App\Repository\CityRepository;
use App\Repository\FriendshipRepository;
use App\Service\AchievementService;
use App\Service\AvatarService;
use App\Service\EscapeService;
use App\Service\ListService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route("/api/website/routes", name: "app_website_routes_")]
#[IsGranted("ROLE_USER")]
class WebsiteController extends AbstractController
{
    private UserService $userService;
    private EscapeService $escapeService;
    private ListService $listService;
    private AvatarService $avatarService;
    private AchievementService $achievementService;
    private CityRepository $cityRep;
    private FriendshipRepository $friendshipRep;
    private SerializerInterface $serializer;

    public function __construct(
        UserService $userService,
        EscapeService $escapeService,
        ListService $listService,
        AvatarService $avatarService,
        AchievementService $achievementService,
        CityRepository $cityRep,
        FriendshipRepository $friendshipRep,
        SerializerInterface $serializer
    ) {
        $this->userService = $userService;
        $this->escapeService = $escapeService;
        $this->listService = $listService;
        $this->avatarService = $avatarService;
        $this->achievementService = $achievementService;
        $this->cityRep = $cityRep;
        $this->friendshipRep = $friendshipRep;
        $this->serializer = $serializer;
    }

    /**
     * Route for escape's details
     *
     * @param Escape $escape escape to show
     *
     * @api GET
     *
     * @return JsonResponse
     */
    #[Route("/escape/{id}", name: "escape_details", methods: ["GET"])]
    public function getEscape(Escape $escape): JsonResponse
    {
        if (!($user = $this->userService->getRealCurrentUser()) instanceof User) {
            return new JsonResponse(["message" => $user], Response::HTTP_BAD_REQUEST);
        }
        if (($city = $user->getCity()) === null) {
            return new JsonResponse(["message" => "No city found."], Response::HTTP_BAD_REQUEST);
        }

        $description = $this->escapeService->getFirstDescription($city, $escape);
        $link = $this->escapeService->getFirstLink($city, $escape);
        $tmp = $this->escapeService->getAverageAndVotes($escape);
        $isToDo = $this->escapeService->knowIfIsToDo($user, $escape) ?? false;
        $isFavorite = $this->escapeService->knowIfIsFavorite($user, $escape) ?? false;
        $userGrade = $this->escapeService->getUserGrade($user, $escape);

        $usersList = $this->userService->getListUsers();
        $sessions = $this->listService->getSessionsForEscape($user, $escape);

        // Merge data
        $data = array_merge(
            ["escape" => $escape],
            ["description" => $description],
            ["link" => $link],
            ["average" => $tmp["average"]],
            ["votes" => $tmp["votes"]],
            ["isToDo" => $isToDo],
            ["isFavorite" => $isFavorite],
            ["userGrade" => $userGrade],
            ["usersList" => $usersList],
            ["sessions" => $sessions]
        );
        $json = $this->serializer->serialize($data, "json", ["groups" => "routeEscape"]);

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }

    /**
     * Route for escape's details with entreprise
     *
     * @param Escape $escape escape to show
     * @param Entreprise $entreprise entreprise of the escape
     *
     * @api GET
     *
     * @return JsonResponse
     */
    #[Route("/escape/{id}/entreprise/{entreprise}", name: "escape_entreprise_details", methods: ["GET"])]
    public function getEscapeByEntreprise(Escape $escape, Entreprise $entreprise): JsonResponse
    {
        if (!($user = $this->userService->getRealCurrentUser()) instanceof User) {
            return new JsonResponse(["message" => $user], Response::HTTP_BAD_REQUEST);
        }
        if (($city = $user->getCity()) === null) {
            return new JsonResponse(["message" => "No city found."], Response::HTTP_BAD_REQUEST);
        }

        $description = $this->escapeService->getDescriptionOfEntreprise($city, $escape, $entreprise);
        $link = $this->escapeService->getLinkOfEntreprise($city, $escape, $entreprise);
        $tmp = $this->escapeService->getAverageAndVotes($escape);
        $isToDo = $this->escapeService->knowIfIsToDo($user, $escape) ?? false;
        $isFavorite = $this->escapeService->knowIfIsFavorite($user, $escape) ?? false;
        $userGrade = $this->escapeService->getUserGrade($user, $escape);

        $usersList = $this->userService->getListUsers();
        $sessions = $this->listService->getSessionsForEscape($user, $escape);

        // Merge data
        $data = array_merge(
            ["escape" => $escape],
            ["description" => $description],
            ["link" => $link],
            ["average" => $tmp["average"]],
            ["votes" => $tmp["votes"]],
            ["isToDo" => $isToDo],
            ["isFavorite" => $isFavorite],
            ["userGrade" => $userGrade],
            ["usersList" => $usersList],
            ["sessions" => $sessions]
        );
        $json = $this->serializer->serialize($data, "json", ["groups" => "routeEscape"]);

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }

    /**
     * Route for current user's achievements
     *
     * @api GET
     *
     * @return JsonResponse
     */
    #[Route("/achievements", name: "achievements", methods: ["GET"])]
    public function getAchievements(): JsonResponse
    {
        if (!($user = $this->userService->getRealCurrentUser()) instanceof User) {
            return new JsonResponse(["message" => $user], Response::HTTP_BAD_REQUEST);
        }

        $avatar = $this->avatarService->getUserAvatar($user);
        if (getType($avatar) === "string") {
            return new JsonResponse(["message" => $avatar], Response::HTTP_BAD_REQUEST);
        }
        $unlockedAchievements = $this->achievementService->getUnlockedAchievements($user);
        $achievementsToUnlocked = $this->achievementService->getAchievementsToUnlock($user);
        $objects3Dunlocked = $this->achievementService->getUnlockedObjects3D($user);
        $titles = $this->achievementService->getUnlockedTitltes($user);

        // Merge data
        $data = array_merge(
            ["avatar" => $avatar],
            ["unlocked" => $unlockedAchievements],
            ["locked" => $achievementsToUnlocked],
            ["object3D" => $objects3Dunlocked],
            ["titles" => $titles]
        );

        $json = $this->serializer->serialize($data, "json", ["groups" => "routeAchievements"]);

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }

    /**
     * Route for current user's lists
     *
     * @api GET
     *
     * @return JsonResponse
     */
    #[Route("/lists", name: "lists", methods: ["GET"])]
    public function getLists(): JsonResponse
    {
        if (!($user = $this->userService->getRealCurrentUser()) instanceof User) {
            return new JsonResponse(["message" => $user], Response::HTTP_BAD_REQUEST);
        }

        $favoris = $this->listService->getUserFavoris($user);
        $toDo = $this->listService->getUserToDos($user);
        $sessions = $this->listService->getUserSessions($user);

        // Merge data
        $data = array_merge(
            ["favoris" => $favoris],
            ["toDo" => $toDo],
            ["sessions" => $sessions]
        );

        $json = $this->serializer->serialize($data, "json", ["groups" => "routeLists"]);

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }

    /**
     * Route for current user's informations
     *
     * @api GET
     *
     * @return JsonResponse
     */
    #[Route("/informations/user", name: "informations_user", methods: ["GET"])]
    public function getInformationsCurrentUser(): JsonResponse
    {
        if (!($user = $this->userService->getRealCurrentUser()) instanceof User) {
            return new JsonResponse(["message" => $user], Response::HTTP_BAD_REQUEST);
        }

        $cities = $this->cityRep->findAll();
        $unlockedPic = $this->achievementService->getUnlockedElements($user, "image");

        // Merge data
        $data = array_merge(
            ["user" => $user],
            ["cities" => $cities],
            ["pictures" => $unlockedPic]
        );

        $json = $this->serializer->serialize($data, "json", ["groups" => "getInformationsCurrentUser"]);

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }

    /**
     * Route for user's informations
     *
     * @param User $user user to return
     *
     * @api GET
     *
     * @return JsonResponse
     */
    #[Route("/user/{id}", name: "informations_user", methods: ["GET"])]
    public function getProfilUser(User $user): JsonResponse
    {
        if (!($current = $this->userService->getRealCurrentUser()) instanceof User) {
            return new JsonResponse(["message" => $current], Response::HTTP_BAD_REQUEST);
        }

        $friendship = $this->friendshipRep->searchStatusFriendship($current, $user);
        $avatar = $this->avatarService->getUserAvatar($user);

        // Merge data
        $data = array_merge(
            ["user" => $user],
            ["friendship" => $friendship],
            ["avatar" => $avatar]
        );

        $json = $this->serializer->serialize($data, "json", ["groups" => "routeAlterUser"]);

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }
}
