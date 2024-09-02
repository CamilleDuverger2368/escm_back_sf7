<?php

namespace App\Controller\API\Global;

use App\Entity\Friendship;
use App\Entity\User;
use App\Service\AchievementService;
use App\Service\UserService;
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
    private EntityManagerInterface $em;
    private AchievementService $achievementService;

    public function __construct(
        SerializerInterface $serializer,
        UserService $userService,
        EntityManagerInterface $em,
        AchievementService $achievementService
    ) {
        $this->serializer = $serializer;
        $this->userService = $userService;
        $this->em = $em;
        $this->achievementService = $achievementService;
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
        if (!($user = $this->userService->getRealCurrentUser()) instanceof User) {
            return new JsonResponse(["message" => $user], Response::HTTP_BAD_REQUEST);
        }

        $search = $request->query->get("search");

        $users = $this->userService->getUsersByNameOrPseudo($search, $user);

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
        if (!($user = $this->userService->getRealCurrentUser()) instanceof User) {
            return new JsonResponse(["message" => $user], Response::HTTP_BAD_REQUEST);
        }

        $asking = $this->userService->friendRequest($user, $receiver);

        $this->em->persist($asking);
        $this->em->flush();

        $json = $this->serializer->serialize($asking, "json", ["groups" => "getAlterUser"]);

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
        if (!($user = $this->userService->getRealCurrentUser()) instanceof User) {
            return new JsonResponse(["message" => $user], Response::HTTP_BAD_REQUEST);
        }

        $this->em->remove($friendship);
        $this->em->flush();

        // Check achievements
        if (count($achievements = $this->achievementService->hasAchievementToUnlock("social", $user)) > 0) {
            $this->achievementService->checkToUnlockAchievements($user, $achievements);
        }

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
        if (!($user = $this->userService->getRealCurrentUser()) instanceof User) {
            return new JsonResponse(["message" => $user], Response::HTTP_BAD_REQUEST);
        }

        $friendship->setFriend(true);
        $friendship->setSince(new \DateTime());

        $this->em->persist($friendship);
        $this->em->flush();

        // Check achievements
        if (count($achievements = $this->achievementService->hasAchievementToUnlock("social", $user)) > 0) {
            $this->achievementService->checkToUnlockAchievements($user, $achievements);
        }

        return new JsonResponse(null, Response::HTTP_OK);
    }

    /**
     * Get all requests and friendships
     *
     * @api GET
     *
     * @return JsonResponse
     */
    #[Route("/list", name: "list", methods: ["GET"])]
    public function getRequestsAndFriendships(): JsonResponse
    {
        if (!($user = $this->userService->getRealCurrentUser()) instanceof User) {
            return new JsonResponse(["message" => $user], Response::HTTP_BAD_REQUEST);
        }

        $data = $this->userService->getRequestsAndFriendships($user);
        $json = $this->serializer->serialize($data, "json", ["groups" => "getRequestsAndFriendships"]);

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }
}
