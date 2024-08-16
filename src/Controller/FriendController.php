<?php

namespace App\Controller;

use App\Entity\Friendship;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserService;
use Doctrine\Common\Cache\FlushableCache;
use Doctrine\ORM\EntityManagerInterface;
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
    private EntityManagerInterface $em;


    public function __construct(
        SerializerInterface $serializer,
        UserService $userService,
        UserRepository $userRep,
        EntityManagerInterface $em
    ) {
        $this->serializer = $serializer;
        $this->userService = $userService;
        $this->userRep = $userRep;
        $this->em = $em;
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

        $json = $this->userService->friendRequest($realUser, $receiver);

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }

    /**
     * Decline request friendship
     *
     * @param Friendship $friendship to decline
     *
     * @api DELETE
     *
     * @return JsonResponse
     */
    #[Route("/decline/{id}", name: "decline", methods: ["DELETE"])]
    public function declineRequestFriendship(Friendship $friendship): JsonResponse
    {
        if (null === $user = $this->getUser()) {
            return new JsonResponse(["message" => "curent user not found", Response::HTTP_UNAUTHORIZED]);
        }

        $this->em->remove($friendship);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Accept request friendship
     *
     * @param Friendship $friendship to accept
     *
     * @api PUT
     *
     * @return JsonResponse
     */
    #[Route("/accept/{id}", name: "accept", methods: ["PUT"])]
    public function acceptRequestFriendship(Friendship $friendship): JsonResponse
    {
        if (null === $user = $this->getUser()) {
            return new JsonResponse(["message" => "curent user not found", Response::HTTP_UNAUTHORIZED]);
        }

        $friendship->setFriend(true);
        $friendship->setSince(new \DateTime());
        $this->em->persist($friendship);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_OK);
    }
}
