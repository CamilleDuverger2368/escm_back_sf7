<?php

namespace App\Controller;

use App\Entity\Avatar;
use App\Entity\User;
use App\Repository\AvatarRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route("/api/avatar", name: "app_avatar_")]
#[IsGranted("ROLE_USER")]
class AvatarController extends AbstractController
{
    private AvatarRepository $avatarRep;
    private SerializerInterface $serializer;
    private EntityManagerInterface $em;
    private Security $security;
    private UserRepository $userRep;

    private const ELEMENTS = ["Title", "Hat", "Suit", "Goodie"];
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
                              "Tshirt"
                            ];

    public function __construct(
        AvatarRepository $avatarRep,
        SerializerInterface $serializer,
        Security $security,
        EntityManagerInterface $em,
        UserRepository $userRep
    ) {
        $this->avatarRep = $avatarRep;
        $this->serializer = $serializer;
        $this->em = $em;
        $this->security = $security;
        $this->userRep = $userRep;
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
        $avatar = new Avatar();
        $avatar->setCreatedAt(new \DateTimeImmutable());
        $avatar->setUser($user);
        $this->em->persist($avatar);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_CREATED);
    }

    /**
     * Return avatar by user's id
     *
     * @param User $user user's id
     *
     * @api GET
     *
     * @return JsonResponse
     */
    #[Route("/find/{id}", name:"find", methods: ["GET"])]
    public function getAvatarByUserId(User $user): JsonResponse
    {
        $avatar = $this->avatarRep->findOneBy(["user" => $user]);
        if ($avatar === null) {
            return new JsonResponse(["message" => "Avatar not found."], Response::HTTP_BAD_REQUEST);
        }
        $json = $this->serializer->serialize($avatar, "json", ["groups" => "getAvatar"]);

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
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
        if (!$user = $this->security->getUser()) {
            return new JsonResponse(["message" => "There is no current user."], Response::HTTP_BAD_REQUEST);
        }
        if (null === $realUser = $this->userRep->findOneBy(["email" => $user->getUserIdentifier()])) {
            return new JsonResponse(["message" => "curent user not found", Response::HTTP_BAD_REQUEST]);
        }
        $avatar = $this->avatarRep->findOneBy(["user" => $realUser]);
        if ($avatar === null) {
            return new JsonResponse(["message" => "Avatar not found."], Response::HTTP_BAD_REQUEST);
        }
        $json = $this->serializer->serialize($avatar, "json", ["groups" => "getAvatar"]);

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }

    /**
     * Update avatar
     *
     * @param Avatar $avatar avatar's id
     *
     * @api PUT
     *
     * @return JsonResponse
     */
    #[Route("/dress/{id}/{element}/{name}", name:"find", methods: ["PUT"])]
    public function dressAvatar(Avatar $avatar, string $element, string $name): JsonResponse
    {
        if (!$user = $this->security->getUser()) {
            return new JsonResponse(["message" => "There is no current user."], Response::HTTP_BAD_REQUEST);
        }
        if (null === $realUser = $this->userRep->findOneBy(["email" => $user->getUserIdentifier()])) {
            return new JsonResponse(["message" => "curent user not found", Response::HTTP_BAD_REQUEST]);
        }
        if ($avatar->getUser() !== $realUser) {
            return new JsonResponse(["message" => "This is not user's avatar.", Response::HTTP_BAD_REQUEST]);
        }
        if (!array_search(ucfirst(strtolower($element)), self::ELEMENTS)) {
            return new JsonResponse(["message" => "Element not found."], Response::HTTP_BAD_REQUEST);
        }
        if (!array_search(ucfirst(strtolower($name)), self::TROPHEES)) {
            return new JsonResponse(["message" => "Trophee not found."], Response::HTTP_BAD_REQUEST);
        }
        $setter = "set" . ucfirst(strtolower($element));
        $avatar->{$setter}(ucfirst(strtolower($name)));

        $this->em->persist($avatar);
        $this->em->flush();

        $json = $this->serializer->serialize($avatar, "json", ["groups" => "getAvatar"]);

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }
}
