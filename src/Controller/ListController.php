<?php

namespace App\Controller;

use App\Entity\Escape;
use App\Entity\ListFavori;
use App\Entity\ListToDo;
use App\Repository\ListDoneRepository;
use App\Repository\ListFavoriRepository;
use App\Repository\ListToDoRepository;
use App\Repository\UserRepository;
use App\Service\AchievementService;
use App\Service\ListService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
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
    private ListFavoriRepository $favoriRep;
    private ListToDoRepository $toDoRep;
    private UserRepository $userRep;
    private ListService $listService;
    private AchievementService $achievementService;
    private SerializerInterface $serializer;
    private EntityManagerInterface $em;
    private Security $security;

    public function __construct(
        ListFavoriRepository $favoriRep,
        ListToDoRepository $toDoRep,
        UserRepository $userRep,
        AchievementService $achievementService,
        SerializerInterface $serializer,
        ListService $listService,
        EntityManagerInterface $em,
        Security $security
    ) {
        $this->favoriRep = $favoriRep;
        $this->toDoRep = $toDoRep;
        $this->userRep = $userRep;
        $this->listService = $listService;
        $this->achievementService = $achievementService;
        $this->serializer = $serializer;
        $this->em = $em;
        $this->security = $security;
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
        if (!$user = $this->security->getUser()) {
            return new JsonResponse(["message" => "There is no current user."], Response::HTTP_BAD_REQUEST);
        }
        if (null === $user = $this->userRep->findOneBy(["email" => $user->getUserIdentifier()])) {
            return new JsonResponse(["message" => "Current user not found."], Response::HTTP_BAD_REQUEST);
        }

        $favoris = $this->favoriRep->getByUser($user);
        $json = $this->serializer->serialize($favoris, "json", ["groups" => "getList"]);

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
        if (!$user = $this->security->getUser()) {
            return new JsonResponse(["message" => "There is no current user."], Response::HTTP_BAD_REQUEST);
        }
        if (null === $user = $this->userRep->findOneBy(["email" => $user->getUserIdentifier()])) {
            return new JsonResponse(["message" => "Current user not found."], Response::HTTP_BAD_REQUEST);
        }

        $toDo = $this->toDoRep->getByUser($user);
        $json = $this->serializer->serialize($toDo, "json", ["groups" => "getList"]);

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
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
        if (!$user = $this->security->getUser()) {
            return new JsonResponse(["message" => "There is no current user."], Response::HTTP_BAD_REQUEST);
        }
        if (null === $user = $this->userRep->findOneBy(["email" => $user->getUserIdentifier()])) {
            return new JsonResponse(["message" => "Current user not found."], Response::HTTP_BAD_REQUEST);
        }

        $this->listService->addToFavori($user, $escape);

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
        if (!$user = $this->security->getUser()) {
            return new JsonResponse(["message" => "There is no current user."], Response::HTTP_BAD_REQUEST);
        }
        if (null === $user = $this->userRep->findOneBy(["email" => $user->getUserIdentifier()])) {
            return new JsonResponse(["message" => "Current user not found."], Response::HTTP_BAD_REQUEST);
        }

        if ($escape->isActual()) {
            $this->listService->addToToDo($user, $escape);
        }

        // Check achievements
        if (count($achievements = $this->achievementService->hasAchievementToUnlock("list", $user)) > 0) {
            $this->achievementService->checkToUnlockAchievements($user, $achievements);
        }

        return new JsonResponse(null, Response::HTTP_CREATED);
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
        if (!$user = $this->security->getUser()) {
            return new JsonResponse(["message" => "There is no current user."], Response::HTTP_BAD_REQUEST);
        }
        if (null === $user = $this->userRep->findOneBy(["email" => $user->getUserIdentifier()])) {
            return new JsonResponse(["message" => "Current user not found."], Response::HTTP_BAD_REQUEST);
        }

        $this->em->remove($favori);
        $this->em->flush();

        $favoris = $this->favoriRep->getByUser($user);
        $json = $this->serializer->serialize($favoris, "json", ["groups" => "getList"]);

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
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
        if (!$user = $this->security->getUser()) {
            return new JsonResponse(["message" => "There is no current user."], Response::HTTP_BAD_REQUEST);
        }
        if (null === $user = $this->userRep->findOneBy(["email" => $user->getUserIdentifier()])) {
            return new JsonResponse(["message" => "Current user not found."], Response::HTTP_BAD_REQUEST);
        }

        $this->em->remove($todo);
        $this->em->flush();

        $toDos = $this->toDoRep->getByUser($user);
        $json = $this->serializer->serialize($toDos, "json", ["groups" => "getList"]);

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
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
        if (!$user = $this->security->getUser()) {
            return new JsonResponse(["message" => "There is no current user."], Response::HTTP_BAD_REQUEST);
        }
        if (null === $user = $this->userRep->findOneBy(["email" => $user->getUserIdentifier()])) {
            return new JsonResponse(["message" => "Current user not found."], Response::HTTP_BAD_REQUEST);
        }

        $now = new DateTime("now");
        $toDo->setSince($now);

        $this->em->persist($toDo);
        $this->em->flush();

        $toDos = $this->toDoRep->getByUser($user);
        $json = $this->serializer->serialize($toDos, "json", ["groups" => "getList"]);

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
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
        if (!$user = $this->security->getUser()) {
            return new JsonResponse(["message" => "There is no current user."], Response::HTTP_BAD_REQUEST);
        }
        if (null === $realUser = $this->userRep->findOneBy(["email" => $user->getUserIdentifier()])) {
            return new JsonResponse(["message" => "Current user not found."], Response::HTTP_BAD_REQUEST);
        }

        if ($message = $this->listService->addSession($realUser, $request->toArray())) {
            return new JsonResponse(["message" => $message], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(null, Response::HTTP_CREATED);
    }

    /**
     * Get done sessions of current user
     *
     * @api GET
     *
     * @return JsonResponse
     */
    #[Route("/session", name: "list-session", methods: ["GET"])]
    public function getSessions(): JsonResponse
    {
        if (!$user = $this->security->getUser()) {
            return new JsonResponse(["message" => "There is no current user."], Response::HTTP_BAD_REQUEST);
        }
        if (null === $realUser = $this->userRep->findOneBy(["email" => $user->getUserIdentifier()])) {
            return new JsonResponse(["message" => "Current user not found."], Response::HTTP_BAD_REQUEST);
        }

        $json = $this->listService->getSessions($realUser);

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }
}
