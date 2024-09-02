<?php

namespace App\Controller\API\Global;

use App\Entity\DoneSession;
use App\Entity\Escape;
use App\Entity\ListFavori;
use App\Entity\ListToDo;
use App\Entity\User;
use App\Service\AchievementService;
use App\Service\EscapeService;
use App\Service\ListService;
use App\Service\UserService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route("/api/lists", name: "app_lists_")]
#[IsGranted("ROLE_USER")]
class ListController extends AbstractController
{
    private ListService $listService;
    private AchievementService $achievementService;
    private SerializerInterface $serializer;
    private EntityManagerInterface $em;
    private UserService $userService;
    private EscapeService $escapeService;

    public function __construct(
        AchievementService $achievementService,
        SerializerInterface $serializer,
        ListService $listService,
        EntityManagerInterface $em,
        UserService $userService,
        EscapeService $escapeService
    ) {
        $this->listService = $listService;
        $this->achievementService = $achievementService;
        $this->serializer = $serializer;
        $this->em = $em;
        $this->userService = $userService;
        $this->escapeService = $escapeService;
    }

    /**
     * Return all current user's favoris
     *
     * @api GET
     *
     * @return JsonResponse
     */
    #[Route("/favoris", name: "favoris", methods: ["GET"])]
    public function getUserFavoris(): JsonResponse
    {
        if (!($user = $this->userService->getRealCurrentUser()) instanceof User) {
            return new JsonResponse(["message" => $user], Response::HTTP_BAD_REQUEST);
        }

        $favoris = $this->listService->getUserFavoris($user);

        $json = $this->serializer->serialize($favoris, "json", ["groups" => "routeLists"]);

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }

    /**
     * Return all current user's to-do
     *
     * @api GET
     *
     * @return JsonResponse
     */
    #[Route("/to-do", name: "to-do", methods: ["GET"])]
    public function getUserToDo(): JsonResponse
    {
        if (!($user = $this->userService->getRealCurrentUser()) instanceof User) {
            return new JsonResponse(["message" => $user], Response::HTTP_BAD_REQUEST);
        }

        $toDo = $this->listService->getUserToDos($user);

        $json = $this->serializer->serialize($toDo, "json", ["groups" => "routeLists"]);

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }

    /**
     * Get to-do list of current user for an escape
     *
     * @api GET
     *
     * @return JsonResponse
     */
    #[Route("/to-do/{id}", name: "escape-to-do", methods: ["GET"])]
    public function getToDoEscape(Escape $escape): JsonResponse
    {
        if (!($user = $this->userService->getRealCurrentUser()) instanceof User) {
            return new JsonResponse(["message" => $user], Response::HTTP_BAD_REQUEST);
        }

        $isToDo = $this->escapeService->knowIfIsToDo($user, $escape) ?? false;

        // Merge Data
        $data = [
            "toDoList" => $escape->getListToDos(),
            "isToDo" => $isToDo
        ];

        $json = $this->serializer->serialize($data, "json", ["groups" => "routeEscape"]);

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }

    /**
     * Get all done sessions of current user
     *
     * @api GET
     *
     * @return JsonResponse
     */
    #[Route("/session", name: "list-session", methods: ["GET"])]
    public function getUserSessions(): JsonResponse
    {
        if (!($user = $this->userService->getRealCurrentUser()) instanceof User) {
            return new JsonResponse(["message" => $user], Response::HTTP_BAD_REQUEST);
        }

        $sessions = $this->listService->getUserSessions($user);

        $json = $this->serializer->serialize($sessions, "json", ["groups" => "routeLists"]);

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }

    /**
     * Get done sessions of current user for an escape
     *
     * @api GET
     *
     * @return JsonResponse
     */
    #[Route("/session/{id}", name: "escape-session", methods: ["GET"])]
    public function getSessionsOfEscape(Escape $escape): JsonResponse
    {
        if (!($user = $this->userService->getRealCurrentUser()) instanceof User) {
            return new JsonResponse(["message" => $user], Response::HTTP_BAD_REQUEST);
        }

        $sessions = $this->listService->getSessionsForEscape($user, $escape);

        $json = $this->serializer->serialize($sessions, "json", ["groups" => "routeEscape"]);

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }

    /**
     * Remove escape from current user's favori
     *
     * @param ListFavori $favori favori to remove
     *
     * @api DELETE
     *
     * @return JsonResponse
     */
    #[Route("/favoris/remove/{id}", name: "remove_from_favori", methods: ["DELETE"])]
    public function removeUserFavori(ListFavori $favori): JsonResponse
    {
        if (!($user = $this->userService->getRealCurrentUser()) instanceof User) {
            return new JsonResponse(["message" => $user], Response::HTTP_BAD_REQUEST);
        }

        $this->em->remove($favori);
        $this->em->flush();

        // Check achievements
        if (count($achievements = $this->achievementService->hasAchievementToUnlock("list", $user)) > 0) {
            $this->achievementService->checkToUnlockAchievements($user, $achievements);
        }

        return new JsonResponse(null, Response::HTTP_OK);
    }

    /**
     * Remove escape from current user's to-do
     *
     * @param ListToDo $todo to-do to remove
     *
     * @api DELETE
     *
     * @return JsonResponse
     */
    #[Route("/to-do/remove/{id}", name: "remove_from_to-do", methods: ["DELETE"])]
    public function removeUserToDo(ListToDo $todo): JsonResponse
    {
        if (!($user = $this->userService->getRealCurrentUser()) instanceof User) {
            return new JsonResponse(["message" => $user], Response::HTTP_BAD_REQUEST);
        }

        $this->em->remove($todo);
        $this->em->flush();

        // Check achievements
        if (count($achievements = $this->achievementService->hasAchievementToUnlock("list", $user)) > 0) {
            $this->achievementService->checkToUnlockAchievements($user, $achievements);
        }

        return new JsonResponse(null, Response::HTTP_OK);
    }

    /**
     * Delete current user's session
     *
     * @api DELETE
     *
     * @return JsonResponse
     */
    #[Route("/session/remove/{id}", name: "remove-session", methods: ["DELETE"])]
    public function removeUserSession(DoneSession $session): JsonResponse
    {
        if (!($user = $this->userService->getRealCurrentUser()) instanceof User) {
            return new JsonResponse(["message" => $user], Response::HTTP_BAD_REQUEST);
        }

        $this->em->remove($session);
        $this->em->flush();

        // Check achievements
        if (count($achievements = $this->achievementService->hasAchievementToUnlock("list", $user)) > 0) {
            $this->achievementService->checkToUnlockAchievements($user, $achievements);
        }

        return new JsonResponse(null, Response::HTTP_OK);
    }

    /**
     * Add escape to current user's favori
     *
     * @param Escape $escape escape to add
     *
     * @api POST
     *
     * @return JsonResponse
     */
    #[Route("/favoris/add/{id}", name: "add_to_favori", methods: ["POST"])]
    public function addUserFavori(Escape $escape): JsonResponse
    {
        if (!($user = $this->userService->getRealCurrentUser()) instanceof User) {
            return new JsonResponse(["message" => $user], Response::HTTP_BAD_REQUEST);
        }

        $favori = $this->listService->addToFavori($user, $escape);

        if ($favori !== null) {
            $this->em->persist($favori);
            $this->em->flush();

            // Check achievements
            if (count($achievements = $this->achievementService->hasAchievementToUnlock("list", $user)) > 0) {
                $this->achievementService->checkToUnlockAchievements($user, $achievements);
            }
        }

        return new JsonResponse(null, Response::HTTP_CREATED);
    }

    /**
     * Add escape to current user's to-do
     *
     * @param Escape $escape escape to add
     *
     * @api POST
     *
     * @return JsonResponse
     */
    #[Route("/to-do/add/{id}", name: "add_to_to-do", methods: ["POST"])]
    public function addUserToDo(Escape $escape): JsonResponse
    {
        if (!($user = $this->userService->getRealCurrentUser()) instanceof User) {
            return new JsonResponse(["message" => $user], Response::HTTP_BAD_REQUEST);
        }

        if ($escape->isActual()) {
            $toDo = $this->listService->addToToDo($user, $escape);

            if ($toDo !== null) {
                $this->em->persist($toDo);
                $this->em->flush();

                // Check achievements
                if (count($achievements = $this->achievementService->hasAchievementToUnlock("list", $user)) > 0) {
                    $this->achievementService->checkToUnlockAchievements($user, $achievements);
                }
            }
        }

        return new JsonResponse(null, Response::HTTP_CREATED);
    }

    /**
     * Add new session
     *
     * @param Request $request request object
     *
     * @api POST
     *
     * @return JsonResponse
     */
    #[Route("/session/add", name: "add-session", methods: ["POST"])]
    public function addSession(Request $request): JsonResponse
    {
        if (!($user = $this->userService->getRealCurrentUser()) instanceof User) {
            return new JsonResponse(["message" => $user], Response::HTTP_BAD_REQUEST);
        }

        if (!($session = $this->listService->addSession($user, $request->toArray())) instanceof DoneSession) {
            return new JsonResponse(["message" => $session], Response::HTTP_BAD_REQUEST);
        }

        $this->em->persist($session);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_CREATED);
    }

    /**
     * Actualise current user's to-do
     *
     * @param ListToDo $toDo to-do to
     *
     * @api PUT
     *
     * @return JsonResponse
     */
    #[Route("/to-do/update/{id}", name: "update_to-do", methods: ["PUT"])]
    public function updateUserToDo(ListToDo $toDo): JsonResponse
    {
        if (!($user = $this->userService->getRealCurrentUser()) instanceof User) {
            return new JsonResponse(["message" => $user], Response::HTTP_BAD_REQUEST);
        }

        $now = new DateTime("now");
        $toDo->setSince($now);

        $this->em->persist($toDo);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_OK);
    }
}
