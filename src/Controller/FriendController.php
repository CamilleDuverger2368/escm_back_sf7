<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route("/api/friend", name: "app_friend_")]
#[IsGranted("ROLE_USER")]
class FriendController extends AbstractController
{
    private SerializerInterface $serializer;
    private UserService $userService;
    private UserRepository $userRep;


    public function __construct(
        SerializerInterface $serializer,
        UserService $userService,
        UserRepository $userRep
    ) {
        $this->serializer = $serializer;
        $this->userService = $userService;
        $this->userRep = $userRep;
    }

    /**
     * Find friends
     *
     * @param Request $request request's object
     *
     * @api GET
     *
     * @return JsonResponse
     */
    #[Route("/finder", name: "finder", methods: ["GET"])]
    public function findFriends(Request $request): JsonResponse
    {
        if (null === $user = $this->getUser()) {
            return new JsonResponse(["message" => "curent user not found", Response::HTTP_UNAUTHORIZED]);
        }
        if (null === $realUser = $this->userRep->findOneBy(["email" => $user->getUserIdentifier()])) {
            return new JsonResponse(["message" => "curent user not found", Response::HTTP_BAD_REQUEST]);
        }

        $search = $request->query->get("search");

        $users = $this->userService->getUsersByNameOrPseudo($search, $realUser);

        $json = $this->serializer->serialize($users, "json", ["groups" => "findFriends"]);

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }

    /**
     * Request friendship
     *
     * @param User $user receiver
     *
     * @api POST
     *
     * @return JsonResponse
     */
    #[Route("/asking/{id}", name: "asking", methods: ["POST"])]
    public function askingForFriendship(User $receiver): JsonResponse
    {
        if (null === $user = $this->getUser()) {
            return new JsonResponse(["message" => "curent user not found", Response::HTTP_UNAUTHORIZED]);
        }
        if (null === $realUser = $this->userRep->findOneBy(["email" => $user->getUserIdentifier()])) {
            return new JsonResponse(["message" => "curent user not found", Response::HTTP_BAD_REQUEST]);
        }

        $this->userService->friendRequest($realUser, $receiver);

        return new JsonResponse(null, Response::HTTP_CREATED);
    }
}
