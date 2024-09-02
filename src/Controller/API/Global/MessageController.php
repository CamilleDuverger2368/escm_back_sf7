<?php

namespace App\Controller\API\Global;

use App\Entity\Room;
use App\Entity\User;
use App\Repository\MessageRepository;
use App\Service\MessageService;
use App\Service\RoomService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route("/api/messages", name: "app_messages_")]
#[IsGranted("ROLE_USER")]
class MessageController extends AbstractController
{
    private SerializerInterface $serializer;
    private MessageService $messageService;
    private RoomService $roomService;
    private UserService $userService;
    private MessageRepository $messageRep;

    public function __construct(
        MessageRepository $messageRep,
        MessageService $messageService,
        RoomService $roomService,
        UserService $userService,
        SerializerInterface $serializer
    ) {
        $this->messageRep = $messageRep;
        $this->messageService = $messageService;
        $this->roomService = $roomService;
        $this->userService = $userService;
        $this->serializer = $serializer;
    }

    /**
     * Get all unread message and rooms of current user
     *
     * @api GET
     *
     * @return JsonResponse
     */
    #[Route("/unread", name:"unread_room", methods: ["GET"])]
    public function getRoomsAndUnreadMessages(): JsonResponse
    {
        if (!($user = $this->userService->getRealCurrentUser()) instanceof User) {
            return new JsonResponse(["message" => $user], Response::HTTP_BAD_REQUEST);
        }
        $rooms = $this->messageService->getRoomsAndUnreadMessages($user);
        $json = $this->serializer->serialize($rooms, "json", ["groups" => "getRoom"]);
        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }

    /**
     * Send a message
     *
     * @param Request $request
     * @param Room $room message's room
     *
     * @api POST
     *
     * @return JsonResponse
     */
    #[Route("/send/{id}", name:"send", methods: ["POST"])]
    public function sendMessage(Room $room, Request $request): JsonResponse
    {
        if (!($user = $this->userService->getRealCurrentUser()) instanceof User) {
            return new JsonResponse(["message" => $user], Response::HTTP_BAD_REQUEST);
        }
        if ($this->roomService->isMember($user, $room) === false) {
            return new JsonResponse(
                ["message" => "Curent user is not in room " . $room->getId() . "."],
                Response::HTTP_BAD_REQUEST
            );
        }

        $content = $request->toArray();
        if (isset($content["message"]) === false) {
            return new JsonResponse(["message" => "There is no message."], Response::HTTP_BAD_REQUEST);
        }
        $this->messageService->sendMessage($user, $room, $content["message"]);
        $messages = $this->messageRep->getMessagesOfRoom($room);
        $json = $this->serializer->serialize($messages, "json", ["groups" => "getMessages"]);
        return new JsonResponse($json, Response::HTTP_CREATED, ["accept" => "json"], true);
    }

    /**
     * Get all room's message
     *
     * @param Room $room message's room
     *
     * @api GET
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name:"list_room", methods: ["GET"])]
    public function getMessages(Room $room): JsonResponse
    {
        if (!($user = $this->userService->getRealCurrentUser()) instanceof User) {
            return new JsonResponse(["message" => $user], Response::HTTP_BAD_REQUEST);
        }
        if ($this->roomService->isMember($user, $room) === false) {
            return new JsonResponse(
                ["message" => "Curent user is not in room " . $room->getId() . "."],
                Response::HTTP_BAD_REQUEST
            );
        }
        $messages = $this->messageRep->getMessagesOfRoom($room);
        $json = $this->serializer->serialize($messages, "json", ["groups" => "getMessages"]);
        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }

    /**
     * Read all room's unread message
     *
     * @param Room $room message's room
     *
     * @api PUT
     *
     * @return JsonResponse
     */
    #[Route("/read/{id}", name:"read_messages", methods: ["PUT"])]
    public function readMessages(Room $room): JsonResponse
    {
        if (!($user = $this->userService->getRealCurrentUser()) instanceof User) {
            return new JsonResponse(["message" => $user], Response::HTTP_BAD_REQUEST);
        }
        if ($this->roomService->isMember($user, $room) === false) {
            return new JsonResponse(
                ["message" => "Curent user is not in room " . $room->getId() . "."],
                Response::HTTP_BAD_REQUEST
            );
        }
        $this->messageService->readMessagesOfRoom($user, $room);
        return new JsonResponse(null, Response::HTTP_OK);
    }
}
