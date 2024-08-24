<?php

namespace App\Controller;

use App\Entity\Room;
use App\Repository\UserRepository;
use App\Service\AchievementService;
use App\Service\RoomService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route("/api/rooms", name: "app_rooms_")]
#[IsGranted("ROLE_USER")]
class RoomController extends AbstractController
{
    private UserRepository $userRep;
    private RoomService $roomService;
    private UserService $userService;
    private AchievementService $achievementService;
    private Security $security;
    private SerializerInterface $serializer;

    public function __construct(
        UserRepository $userRep,
        RoomService $roomService,
        UserService $userService,
        AchievementService $achievementService,
        Security $security,
        SerializerInterface $serializer
    ) {
        $this->userRep = $userRep;
        $this->roomService = $roomService;
        $this->userService = $userService;
        $this->achievementService = $achievementService;
        $this->security = $security;
        $this->serializer = $serializer;
    }

    /**
     * Return one room
     *
     * @param Room $room room to return
     *
     * @api GET
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name:"one", methods: ["GET"])]
    public function getRoom(Room $room): JsonResponse
    {
        if (!$user = $this->security->getUser()) {
            return new JsonResponse(["message" => "There is no current user."], Response::HTTP_BAD_REQUEST);
        }
        if (null === $user = $this->userRep->findOneBy(["email" => $user->getUserIdentifier()])) {
            return new JsonResponse(["message" => "Current user not found."], Response::HTTP_BAD_REQUEST);
        }

        if ($this->roomService->isMember($user, $room)) {
            $json = $this->serializer->serialize($room, "json", ["groups" => "getRoom"]);
            return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
        }

        return new JsonResponse(["message" => "User is not a member of this room."], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Update room's name
     *
     * @param Room $room room to return
     * @param Request $request
     *
     * @api PUT
     *
     * @return JsonResponse
     */
    #[Route("/update/name/{id}", name:"update_name", methods: ["PUT"])]
    public function udpateRoomName(Room $room, Request $request): JsonResponse
    {
        if (!$user = $this->security->getUser()) {
            return new JsonResponse(["message" => "There is no current user."], Response::HTTP_BAD_REQUEST);
        }
        if (null === $user = $this->userRep->findOneBy(["email" => $user->getUserIdentifier()])) {
            return new JsonResponse(["message" => "Current user not found."], Response::HTTP_BAD_REQUEST);
        }

        $content = $request->toArray();
        isset($content["name"]) ? $name = $content["name"] : $name = null;
        if ($this->roomService->roomNewName($user, $room, $name)) {
            $json = $this->serializer->serialize($room, "json", ["groups" => "getRoom"]);
            return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
        }

        // Check achievements
        if (count($achievements = $this->achievementService->hasAchievementToUnlock("social", $user)) > 0) {
            $this->achievementService->checkToUnlockAchievements($user, $achievements);
        }

        return new JsonResponse(["message" => "User is not a member of this room."], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Add member to a room
     *
     * @param Room $room room to return
     * @param Request $request
     *
     * @api PUT
     *
     * @return JsonResponse
     */
    #[Route("/add/member/{id}", name:"add_member", methods: ["PUT"])]
    public function addRoomMate(Room $room, Request $request): JsonResponse
    {
        if (!$user = $this->security->getUser()) {
            return new JsonResponse(["message" => "There is no current user."], Response::HTTP_BAD_REQUEST);
        }
        if (null === $user = $this->userRep->findOneBy(["email" => $user->getUserIdentifier()])) {
            return new JsonResponse(["message" => "Current user not found."], Response::HTTP_BAD_REQUEST);
        }

        $content = $request->toArray();
        isset($content["member"]) ? $memberId = $content["member"] : $memberId = null;
        if ($message = $this->roomService->addRoomMate($user, $room, $memberId)) {
            return new JsonResponse(["message" => $message], Response::HTTP_BAD_REQUEST);
        }

        // Check achievements
        if (count($achievements = $this->achievementService->hasAchievementToUnlock("social", $user)) > 0) {
            $this->achievementService->checkToUnlockAchievements($user, $achievements);
        }

        $json = $this->serializer->serialize($room, "json", ["groups" => "getRoom"]);
        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }

    /**
     * Granted a member Admin of a room
     *
     * @param Room $room room to return
     * @param Request $request
     *
     * @api PUT
     *
     * @return JsonResponse
     */
    #[Route("/granted/admin/{id}", name:"granted_admin", methods: ["PUT"])]
    public function grantedAdmin(Room $room, Request $request): JsonResponse
    {
        if (!$user = $this->security->getUser()) {
            return new JsonResponse(["message" => "There is no current user."], Response::HTTP_BAD_REQUEST);
        }
        if (null === $user = $this->userRep->findOneBy(["email" => $user->getUserIdentifier()])) {
            return new JsonResponse(["message" => "Current user not found."], Response::HTTP_BAD_REQUEST);
        }

        $content = $request->toArray();
        isset($content["member"]) ? $memberId = $content["member"] : $memberId = null;
        if ($message = $this->roomService->grantedMemberAdmin($user, $room, $memberId)) {
            return new JsonResponse(["message" => $message], Response::HTTP_BAD_REQUEST);
        }

        $json = $this->serializer->serialize($room, "json", ["groups" => "getRoom"]);
        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }

    /**
     * Kick-off a member
     *
     * @param Room $room room to return
     * @param Request $request
     *
     * @api PUT
     *
     * @return JsonResponse
     */
    #[Route("/kick-off/{id}", name:"kick_off", methods: ["PUT"])]
    public function kickOffFrom(Room $room, Request $request): JsonResponse
    {
        if (!$user = $this->security->getUser()) {
            return new JsonResponse(["message" => "There is no current user."], Response::HTTP_BAD_REQUEST);
        }
        if (null === $user = $this->userRep->findOneBy(["email" => $user->getUserIdentifier()])) {
            return new JsonResponse(["message" => "Current user not found."], Response::HTTP_BAD_REQUEST);
        }

        $content = $request->toArray();
        isset($content["member"]) ? $memberId = $content["member"] : $memberId = null;
        if ($message = $this->roomService->kickOffFrom($user, $room, $memberId)) {
            return new JsonResponse(["message" => $message], Response::HTTP_BAD_REQUEST);
        }

        // Check achievements
        if (count($achievements = $this->achievementService->hasAchievementToUnlock("social", $user)) > 0) {
            $this->achievementService->checkToUnlockAchievements($user, $achievements);
        }

        $json = $this->serializer->serialize($room, "json", ["groups" => "getRoom"]);
        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }

    /**
     * Quit a room
     *
     * @param Room $room room to return
     *
     * @api PUT
     *
     * @return JsonResponse
     */
    #[Route("/quit/{id}", name:"quit", methods: ["PUT"])]
    public function quitRoom(Room $room): JsonResponse
    {
        if (!$user = $this->security->getUser()) {
            return new JsonResponse(["message" => "There is no current user."], Response::HTTP_BAD_REQUEST);
        }
        if (null === $user = $this->userRep->findOneBy(["email" => $user->getUserIdentifier()])) {
            return new JsonResponse(["message" => "Current user not found."], Response::HTTP_BAD_REQUEST);
        }

        if ($message = $this->roomService->quitRoom($user, $room)) {
            return new JsonResponse(["message" => $message], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(null, Response::HTTP_OK);
    }

    /**
     * Create one room
     *
     * @param Request $request
     *
     * @api POST
     *
     * @return JsonResponse
     */
    #[Route("/create", name:"create", methods: ["POST"])]
    public function createRoom(Request $request): JsonResponse
    {
        if (!$user = $this->security->getUser()) {
            return new JsonResponse(["message" => "There is no current user."], Response::HTTP_BAD_REQUEST);
        }
        if (null === $user = $this->userRep->findOneBy(["email" => $user->getUserIdentifier()])) {
            return new JsonResponse(["message" => "Current user not found."], Response::HTTP_BAD_REQUEST);
        }

        // Check if all members exist
        $content = $request->toArray();
        $members = $this->userService->getUsersFromId($content["members"]);
        if (gettype($members) === "string") {
            return new JsonResponse(["message" => $members], Response::HTTP_BAD_REQUEST);
        }

        // Check if a room already exist with these members
        if ($room = $this->roomService->isRoomExist($user, $members)) {
            $json = $this->serializer->serialize($room, "json", ["groups" => "getRoom"]);
            return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
        }

        // Create a new room
        $room = new Room();
        isset($content["name"]) ? $name = $content["name"] : $name = null;
        $this->roomService->createRoom($room, $user, $members, $name);

        // Check achievements
        if (count($achievements = $this->achievementService->hasAchievementToUnlock("social", $user)) > 0) {
            $this->achievementService->checkToUnlockAchievements($user, $achievements);
        }

        $json = $this->serializer->serialize($room, "json", ["groups" => "getRoom"]);
        return new JsonResponse($json, Response::HTTP_CREATED, ["accept" => "json"], true);
    }
}
