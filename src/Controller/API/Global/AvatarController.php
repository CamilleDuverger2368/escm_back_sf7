<?php

namespace App\Controller\API\Global;

use App\Entity\Avatar;
use App\Entity\User;
use App\Service\AvatarService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route("/api/avatar", name: "app_avatar_")]
#[IsGranted("ROLE_USER")]
class AvatarController extends AbstractController
{
    private SerializerInterface $serializer;
    private EntityManagerInterface $em;
    private AvatarService $avatarService;
    private UserService $userService;

    private const ELEMENTS = ["Hat", "Suit", "Goodie"];
    private const TROPHEES = ["Glass",
                              "Torchman",
                              "Weapon",
                              "BowlerHat",
                              "Cap",
                              "SherlockHat",
                              "TopHat",
                              "Jogging",
                              "Skirt",
                              "Smoking",
                              "SuitPants",
                              "Sweat",
                              "Tshirt",
                              "Nothing"
                            ];

    public function __construct(
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        AvatarService $avatarService,
        UserService $userService
    ) {
        $this->serializer = $serializer;
        $this->em = $em;
        $this->avatarService = $avatarService;
        $this->userService = $userService;
    }

    /**
     * Return avatar by id
     *
     * @param Avatar $avatar avatar's id
     *
     * @api GET
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name:"unique", methods: ["GET"])]
    public function getAvatar(Avatar $avatar): JsonResponse
    {
        $json = $this->serializer->serialize($avatar, "json", ["groups" => "getAvatar"]);

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }

    /**
     * Create Avatar for user
     *
     * @param User $user user's avatar
     *
     * @api POST
     *
     * @return JsonResponse
     */
    #[Route("/create/{id}", name:"unique", methods: ["POST"])]
    public function createAvatar(User $user): JsonResponse
    {
        $avatar = $this->avatarService->createAvatar($user);
        $this->em->persist($avatar);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_CREATED);
    }

    /**
     * Return current user's avatar
     *
     * @api GET
     *
     * @return JsonResponse
     */
    #[Route("/find", name:"current_avatar_find", methods: ["GET"])]
    public function getCurrentUserAvatar(): JsonResponse
    {
        if (!($user = $this->userService->getRealCurrentUser()) instanceof User) {
            return new JsonResponse(["message" => $user], Response::HTTP_BAD_REQUEST);
        }

        $avatar = $this->avatarService->getUserAvatar($user);
        if (getType($avatar) === "string") {
            return new JsonResponse(["message" => $avatar], Response::HTTP_BAD_REQUEST);
        }
        $json = $this->serializer->serialize($avatar, "json", ["groups" => "getAvatar"]);

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }

    /**
     * Update avatar
     *
     * @param Avatar $avatar avatar's id
     * @param string $element type of dressing
     * @param string $name
     *
     * @api PUT
     *
     * @return JsonResponse
     */
    #[Route("/dress/{id}/{element}/{name}", name:"dress", methods: ["PUT"])]
    public function dressAvatar(Avatar $avatar, string $element, string $name): JsonResponse
    {
        if (!($user = $this->userService->getRealCurrentUser()) instanceof User) {
            return new JsonResponse(["message" => $user], Response::HTTP_BAD_REQUEST);
        }
        if ($avatar->getUser() !== $user) {
            return new JsonResponse(["message" => "This is not user's avatar.", Response::HTTP_BAD_REQUEST]);
        }

        if (false === array_search(ucfirst(strtolower($element)), self::ELEMENTS)) {
            return new JsonResponse(["message" => "Element not found."], Response::HTTP_BAD_REQUEST);
        }
        if (false === array_search(ucfirst(strtolower($name)), self::TROPHEES)) {
            return new JsonResponse(["message" => "Trophee not found."], Response::HTTP_BAD_REQUEST);
        }

        $setter = "set" . ucfirst(strtolower($element));
        if (ucfirst(strtolower($name)) === "Nothing") {
            $avatar->{$setter}('');
        } else {
            $avatar->{$setter}(ucfirst(strtolower($name)));
        }

        $this->em->persist($avatar);
        $this->em->flush();

        return new JsonResponse(["message" => "Avatar updated", Response::HTTP_OK]);
    }

    /**
     * Update avatar's title
     *
     * @param Avatar $avatar avatar's id
     * @param string $name title
     *
     * @api PUT
     *
     * @return JsonResponse
     */
    #[Route("/title/{id}/{name}", name:"title", methods: ["PUT"])]
    public function titleAvatar(Avatar $avatar, string $name): JsonResponse
    {
        if (!($user = $this->userService->getRealCurrentUser()) instanceof User) {
            return new JsonResponse(["message" => $user], Response::HTTP_BAD_REQUEST);
        }
        if ($avatar->getUser() !== $user) {
            return new JsonResponse(["message" => "This is not user's avatar.", Response::HTTP_BAD_REQUEST]);
        }

        $avatar->setTitle($name);

        $this->em->persist($avatar);
        $this->em->flush();

        return new JsonResponse(["message" => "Avatar updated", Response::HTTP_OK]);
    }
}
