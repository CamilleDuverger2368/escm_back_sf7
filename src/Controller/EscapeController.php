<?php

namespace App\Controller;

use App\Entity\Entreprise;
use App\Entity\Escape;
use App\Entity\Grade;
use App\Repository\GradeRepository;
use App\Repository\UserRepository;
use App\Service\AchievementService;
use App\Service\EscapeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route("/api/escape", name: "app_escape_")]
#[IsGranted("ROLE_USER")]
class EscapeController extends AbstractController
{
    private Security $security;
    private SerializerInterface $serializer;
    private EscapeService $escapeService;
    private ValidatorInterface $validator;
    private EntityManagerInterface $em;
    private GradeRepository $gradeRep;
    private AchievementService $achievementService;
    private UserRepository $userRep;


    public function __construct(
        Security $security,
        EscapeService $escapeService,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        EntityManagerInterface $em,
        GradeRepository $gradeRep,
        AchievementService $achievementService,
        UserRepository $userRep
    ) {
        $this->security = $security;
        $this->escapeService = $escapeService;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->em = $em;
        $this->gradeRep = $gradeRep;
        $this->userRep = $userRep;
        $this->achievementService = $achievementService;
    }

    /**
     * Find escapes
     *
     * @param Request $request request's object
     *
     * @api GET
     *
     * @return JsonResponse
     */
    #[Route("/finder", name: "finder", methods: ["GET"])]
    public function findEscapes(Request $request): JsonResponse
    {
        $parameters = [];
        $parameters["nbPlayer"] = intval($request->query->get("nbPlayer"));
        $parameters["price"] = intval($request->query->get("price"));
        $parameters["level"] = intval($request->query->get("level"));
        $parameters["age"] = intval($request->query->get("age"));
        $parameters["time"] = intval($request->query->get("time"));
        $parameters["actual"] = boolval($request->query->get("actual"));

        if (!$user = $this->security->getUser()) {
            return new JsonResponse(["message" => "There is no current user."], Response::HTTP_BAD_REQUEST);
        }
        if (null === $user = $this->userRep->findOneBy(["email" => $user->getUserIdentifier()])) {
            return new JsonResponse(["message" => "Current user not found."], Response::HTTP_BAD_REQUEST);
        }

        if ($city = $user->getCity()) {
            $json = $this->escapeService->findEscapes($city, $parameters);
            return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
        }
        return new JsonResponse(["message" => "Current user's city not found."], Response::HTTP_BAD_REQUEST);
    }


    /**
     * Return an escape by is Id and is entreprise
     *
     * @param Escape $escape escape to show
     * @param Entreprise $entreprise entreprise of the escape
     *
     * @api GET
     *
     * @return JsonResponse
     */
    #[Route("/{id}/entreprise/{entreprise}", name: "entreprise_details", methods: ["GET"])]
    public function getEscapeByEntreprise(Escape $escape, Entreprise $entreprise): JsonResponse
    {
        if (!$user = $this->security->getUser()) {
            return new JsonResponse(["message" => "There is no current user."], Response::HTTP_BAD_REQUEST);
        }
        if (null === $user = $this->userRep->findOneBy(["email" => $user->getUserIdentifier()])) {
            return new JsonResponse(["message" => "Current user not found."], Response::HTTP_BAD_REQUEST);
        }

        $json = $this->escapeService->getInformationsWithEntreprise($user, $escape, $entreprise);

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }

    /**
     * Return an escape by is Id
     *
     * @param Escape $escape escape to show
     *
     * @api GET
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "details", methods: ["GET"])]
    public function getEscape(Escape $escape): JsonResponse
    {
        if (!$user = $this->security->getUser()) {
            return new JsonResponse(["message" => "There is no current user."], Response::HTTP_BAD_REQUEST);
        }
        if (null === $user = $this->userRep->findOneBy(["email" => $user->getUserIdentifier()])) {
            return new JsonResponse(["message" => "Current user not found."], Response::HTTP_BAD_REQUEST);
        }

        $json = $this->escapeService->getInformations($user, $escape);

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }

    /**
     * Grade an escape by current user
     *
     * @param Request $request request's object
     * @param Escape $escape escape to grade
     *
     * @api POST
     *
     * @return JsonResponse
     */
    #[Route("/grade/{id}", name: "grade", methods: ["POST"])]
    public function gradeEscape(Request $request, Escape $escape): JsonResponse
    {
        if (!$user = $this->security->getUser()) {
            return new JsonResponse(["message" => "There is no current user."], Response::HTTP_BAD_REQUEST);
        }
        if (null === $user = $this->userRep->findOneBy(["email" => $user->getUserIdentifier()])) {
            return new JsonResponse(["message" => "Current user not found."], Response::HTTP_BAD_REQUEST);
        }

        $grade = $this->serializer->deserialize($request->getContent(), Grade::class, "json");
        $errors = $this->validator->validate($grade);
        if ($errors->count() > 0) {
            return new JsonResponse($this->serializer->serialize($errors, "json"), Response::HTTP_BAD_REQUEST);
        }

        $grade->setEscape($escape);
        $grade->setUser($user);

        $this->em->persist($grade);
        $this->em->flush();

        // Check achievements
        if (count($achievements = $this->achievementService->hasAchievementToUnlock("grade", $user)) > 0) {
            $this->achievementService->checkToUnlockAchievements($user, $achievements);
        }

        return new JsonResponse(null, Response::HTTP_CREATED);
    }

    /**
     * Update escape's grade by current user
     *
     * @param Request $request request's object
     * @param Escape $escape escape to grade
     *
     * @api PUT
     *
     * @return JsonResponse
     */
    #[Route("/grade/update/{id}", name: "grade_update", methods: ["PUT"])]
    public function updateGradeEscape(Request $request, Escape $escape): JsonResponse
    {
        if (!$user = $this->security->getUser()) {
            return new JsonResponse(["message" => "There is no current user."], Response::HTTP_BAD_REQUEST);
        }
        if (null === $user = $this->userRep->findOneBy(["email" => $user->getUserIdentifier()])) {
            return new JsonResponse(["message" => "Current user not found."], Response::HTTP_BAD_REQUEST);
        }

        if (null === $grade = $this->gradeRep->getGradeByUserAndEscape($user, $escape)) {
            return new JsonResponse(["message" => "Grade not found."], Response::HTTP_BAD_REQUEST);
        }
        $array = $request->toArray();
        if (!$array["grade"] || ($array["grade"] > 5 && $array["grade"] < 0)) {
            return new JsonResponse(["message" => "need valid grade", Response::HTTP_BAD_REQUEST]);
        }
        $grade->setGrade($array["grade"]);

        $this->em->persist($grade);
        $this->em->flush();

        // Check achievements
        if (count($achievements = $this->achievementService->hasAchievementToUnlock("grade", $user)) > 0) {
            $this->achievementService->checkToUnlockAchievements($user, $achievements);
        }

        return new JsonResponse(null, Response::HTTP_CREATED);
    }

    /**
     * Delete escape's grade by current user
     *
     * @param Request $request request's object
     * @param Escape $escape escape of grade to delete
     *
     * @api DELETE
     *
     * @return JsonResponse
     */
    #[Route("/grade/delete/{id}", name: "grade_delete", methods: ["DELETE"])]
    public function deleteGradeEscape(Request $request, Escape $escape): JsonResponse
    {
        if (!$user = $this->security->getUser()) {
            return new JsonResponse(["message" => "There is no current user."], Response::HTTP_BAD_REQUEST);
        }
        if (null === $user = $this->userRep->findOneBy(["email" => $user->getUserIdentifier()])) {
            return new JsonResponse(["message" => "Current user not found."], Response::HTTP_BAD_REQUEST);
        }

        if (null === $grade = $this->gradeRep->getGradeByUserAndEscape($user, $escape)) {
            return new JsonResponse(["message" => "Grade not found."], Response::HTTP_BAD_REQUEST);
        }

        $this->em->remove($grade);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
