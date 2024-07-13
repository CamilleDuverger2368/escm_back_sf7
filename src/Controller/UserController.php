<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\AchievementService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route("/api/user", name: "app_user_")]
#[IsGranted("ROLE_USER")]
class UserController extends AbstractController
{
    private Security $security;
    private EntityManagerInterface $em;
    private SerializerInterface $serializer;
    private UserService $userService;
    private AchievementService $achievementService;
    private UserRepository $userRep;
    private ValidatorInterface $validator;


    public function __construct(
        Security $security,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        UserService $userService,
        AchievementService $achievementService,
        UserRepository $userRep,
        ValidatorInterface $validator
    ) {
        $this->security = $security;
        $this->em = $em;
        $this->serializer = $serializer;
        $this->userService = $userService;
        $this->userRep = $userRep;
        $this->achievementService = $achievementService;
        $this->validator = $validator;
    }

    /**
     * @api PUT
     *
     * @return JsonResponse
     */
    #[Route("/logout", name: "logout", methods: ["PUT"])]
    public function logout(): JsonResponse
    {
        if (null === $user = $this->getUser()) {
            return new JsonResponse(["message" => "curent user not found", Response::HTTP_UNAUTHORIZED]);
        }
        if (null === $realUser = $this->userRep->findOneBy(["email" => $user->getUserIdentifier()])) {
            return new JsonResponse(["message" => "curent user not found", Response::HTTP_BAD_REQUEST]);
        }

        $realUser->setApiToken(null);
        $this->em->persist($realUser);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_OK);
    }

    /**
     * Return current user
     *
     * @api GET
     *
     * @return JsonResponse
     */
    #[Route("", name: "current", methods: ["GET"])]
    public function getCurrentUser(): JsonResponse
    {
        if (!$user = $this->security->getUser()) {
            return new JsonResponse(["message" => "There is no current user."], Response::HTTP_BAD_REQUEST);
        }
        $json = $this->serializer->serialize($user, "json", ["groups" => "getCurrent"]);

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }

    /**
     * Return all users
     *
     * @api GET
     *
     * @return JsonResponse
     */
    #[Route("/list", name: "list", methods: ["GET"])]
    public function getUsers(): JsonResponse
    {
        if (!$this->security->getUser()) {
            return new JsonResponse(["message" => "There is no current user."], Response::HTTP_BAD_REQUEST);
        }
        $users = $this->userRep->findAll();
        $json = $this->serializer->serialize($users, "json", ["groups" => "getListUsers"]);

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }

    /**
     * Update current user
     * @param Request $request request's object
     * @param User $user current user
     *
     * @api PUT
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "update", methods: ["PUT"])]
    public function updateCurrentUser(Request $request, User $user): JsonResponse
    {
        // if (!$user = $this->security->getUser()) {
        //     return new JsonResponse(["message" => "There is no current user."], Response::HTTP_BAD_REQUEST);
        // }

        $userUp = $this->serializer->deserialize(
            $request->getContent(),
            User::class,
            "json",
            [AbstractNormalizer::OBJECT_TO_POPULATE => $user]
        );

        $errors = $this->validator->validate($userUp);
        if ($errors->count() > 0) {
            return new JsonResponse($this->serializer->serialize($errors, "json"), Response::HTTP_BAD_REQUEST);
        }
        if ($message = $this->userService->checkInformationsUserUpdate($userUp, $request->toArray()) !== null) {
            return new JsonResponse(["message" => $message, Response::HTTP_BAD_REQUEST]);
        }

        $this->em->persist($userUp);
        $this->em->flush();

        // Check achievements
        if (count($achievements = $this->achievementService->hasAchievementToUnlock("social", $user)) > 0) {
            $this->achievementService->checkToUnlockAchievements($user, $achievements);
        }

        return new JsonResponse(["message" => "user updated", Response::HTTP_OK]);
    }

    /**
     * Update current user's password
     *
     * @param Request $request request's object
     * @param User $user current user
     *
     * @api PUT
     *
     * @return JsonResponse
     */
    #[Route("/password/{id}", name: "password_update", methods: ["PUT"])]
    public function updatePasswordCurrentUser(Request $request, User $user): JsonResponse
    {
        if (!$user = $this->security->getUser()) {
            return new JsonResponse(["message" => "There is no current user."], Response::HTTP_BAD_REQUEST);
        }
        if (null === $user = $this->userRep->findOneBy(["email" => $user->getUserIdentifier()])) {
            return new JsonResponse(["message" => "Current user not found."], Response::HTTP_BAD_REQUEST);
        }
        $content = $request->toArray();
        if ($message = $this->userService->updatePasswordCurrentUser($user, $content)) {
            return new JsonResponse(["message" => $message, Response::HTTP_BAD_REQUEST]);
        }
        $this->em->persist($user);
        $this->em->flush();

        return new JsonResponse(["message" => "user's password updated", Response::HTTP_OK]);
    }

    /**
     * Return user with this id
     *
     * @param User $user user to return
     *
     * @api GET
     *
     * @return JsonResponse
     */
    #[Route("/alter/{id}", name: "alter", methods: ["GET"])]
    public function getAlterUser(User $user): JsonResponse
    {
        if (!$this->security->getUser()) {
            return new JsonResponse(["message" => "There is no current user."], Response::HTTP_BAD_REQUEST);
        }
        $json = $this->serializer->serialize($user, "json", ["groups" => "getAlterUser"]);

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }
}
