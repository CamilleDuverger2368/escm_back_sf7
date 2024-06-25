<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\CityRepository;
use App\Repository\UserRepository;
use App\Service\MailerService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route("/api/unlog", name: "app_unlog_")]
class UserUnlogController extends AbstractController
{
    private UserRepository $userRep;
    private CityRepository $cityRep;
    private SerializerInterface $serializer;
    private EntityManagerInterface $em;
    private MailerService $mailerService;
    private ValidatorInterface $validator;
    private UserService $userService;

    public function __construct(
        UserRepository $userRep,
        CityRepository $cityRep,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        MailerService $mailerService,
        ValidatorInterface $validator,
        UserService $userService
    ) {
        $this->userRep = $userRep;
        $this->cityRep = $cityRep;
        $this->serializer = $serializer;
        $this->em = $em;
        $this->mailerService = $mailerService;
        $this->validator = $validator;
        $this->userService = $userService;
    }

    /**
     * Create a new user
     *
     * @param Request $request request's object
     *
     * @api POST
     *
     * @return JsonResponse
     */
    #[Route("/register", name: "register", methods: ["POST"])]
    public function registerUser(Request $request): JsonResponse
    {
        $user = $this->serializer->deserialize($request->getContent(), User::class, "json");

        $errors = $this->validator->validate($user);
        if ($errors->count() > 0) {
            return new JsonResponse($this->serializer->serialize($errors, "json"), Response::HTTP_BAD_REQUEST);
        }
        if ($message = $this->userService->checkInformationsUser($user, $request->toArray()) !== null) {
            return new JsonResponse(["message" => $message, Response::HTTP_BAD_REQUEST]);
        }

        $this->mailerService->sendMailRegister($user->getEmail(), $user->getLink());

        $this->em->persist($user);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_CREATED);
    }

    /**
     * Get all cities
     *
     * @api GET
     *
     * @return JsonResponse
     */
    #[Route("/cities", name: "cities", methods: ["GET"])]
    public function getCities(): JsonResponse
    {
        $cities = $this->cityRep->findAll();
        $json = $this->serializer->serialize($cities, "json");

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }

    /**
     * Validate user's email
     *
     * @param string $link user's validation's link
     *
     * @api PUT
     *
     * @return RedirectResponse | JsonResponse
     */
    #[Route("/validation/{link}", name: "validation", methods: ["GET"])]
    public function validateEmail(string $link): RedirectResponse | JsonResponse
    {
        if (!$user = $this->userRep->findOneBy(["link" => $link])) {
            return new JsonResponse(["message" => "can't find this user", Response::HTTP_BAD_REQUEST]);
        }

        $user->setValidated(true);
        $user->setLink($user->getName());
        $user->setRoles(["ROLE_USER"]);

        $this->em->persist($user);
        $this->em->flush();

        $this->mailerService->sendMailConfirm($user->getEmail());

        return $this->redirect('https://harmonious-dolphin-f4601c.netlify.app/login');
    }

    /**
     * Check if email is in DB
     *
     * @param string $email email
     *
     * @api GET
     *
     * @return JsonResponse
     */
    #[Route("/email-exist/{email}", name: "is_email_exist", methods: ["GET"])]
    public function isEmailExist(string $email): JsonResponse
    {
        if ($this->userService->checkIfEmailIsKnown($email) === null) {
            return new JsonResponse(["message" => "unknown email", Response::HTTP_BAD_REQUEST]);
        }

        return new JsonResponse(["message" => "known email", Response::HTTP_OK]);
    }

    /**
     * Send mail to reset password
     *
     * @param string $email email
     *
     * @api PUT
     *
     * @return JsonResponse
     */
    #[Route("/reset-password/{email}", name: "reset_password", methods: ["PUT"])]
    public function getResetPassword(string $email): JsonResponse
    {
        if (($user = $this->userService->checkIfEmailIsKnown($email)) === null) {
            return new JsonResponse(["message" => "unknown email", Response::HTTP_BAD_REQUEST]);
        }

        $password = $this->userService->resetPassword($user);

        $this->mailerService->sendResetPassword($user->getEmail(), $password);

        $this->em->persist($user);
        $this->em->flush();

        return new JsonResponse(["message" => "mail sent", Response::HTTP_OK]);
    }

    /**
     * @api POST
     *
     * @return JsonResponse
     */
    #[Route("/login", name: "login", methods: ["POST"])]
    public function login(): JsonResponse
    {
        if (null === $user = $this->getUser()) {
            return new JsonResponse(["message" => "missing credentials", Response::HTTP_UNAUTHORIZED]);
        }
        if (null === $realUser = $this->userRep->findOneBy(["email" => $user->getUserIdentifier()])) {
            return new JsonResponse(["message" => "curent user not found", Response::HTTP_BAD_REQUEST]);
        }
        if ($realUser->isValidated() === false) {
            return new JsonResponse(["message" => "You have to validate your email.", Response::HTTP_BAD_REQUEST]);
        }

        $token = hash("sha256", uniqid('', true));
        $realUser->setApiToken($token);
        $this->em->persist($realUser);
        $this->em->flush();

        return new JsonResponse(["user" => $realUser->getEmail(), "token" => $token, Response::HTTP_OK]);
    }
}
