<?php

namespace App\Controller;

use App\Entity\Achievement;
use App\Form\AchievementType;
use App\Repository\AchievementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/admin/achievements", name: "admin_achievements_")]
#[IsGranted("ROLE_ADMIN")]
class AdminAchievementController extends AbstractController
{
    private AchievementRepository $achievementRep;
    private EntityManagerInterface $em;

    public function __construct(
        AchievementRepository $achievementRep,
        EntityManagerInterface $em
    ) {
        $this->achievementRep = $achievementRep;
        $this->em = $em;
    }

    /**
     * Add an achievement
     *
     * @return Response
     */
    #[Route("/add", name:"add")]
    public function addAchievement(Request $request): Response
    {
        $achievement = new Achievement();
        $form = $this->createForm(AchievementType::class, $achievement);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $achievement->setCreatedAt(new \DateTimeImmutable());
            $achievement->setUpdatedAt(new \DateTimeImmutable());
            $this->em->persist($achievement);
            $this->em->flush();

            $this->addFlash("success", "Le succès a bien été ajouté.");
            return $this->redirectToRoute("admin_achievements_list");
        }
        return $this->render("achievement/add.html.twig", ["form" => $form->createView()]);
    }

    /**
     * List all Achievements
     *
     * @return Response
     */
    #[Route("", name:"list")]
    public function listAchievements(): Response
    {
        $achievements = $this->achievementRep->findAll();

        return $this->render("achievement/index.html.twig", ["achievements" => $achievements]);
    }

    /**
     * Details one escape
     *
     * @param Request $request request's object
     * @param Achievement $achievement achievement to detail
     *
     * @return Response
     */
    #[Route("/{id}", name:"one")]
    public function showAchievement(Request $request, Achievement $achievement): Response
    {
        return $this->render("achievement/details.html.twig", ["achievement" => $achievement]);
    }

    /**
     * Edit an achievement
     *
     * @param Request $request request's object
     * @param Achievement $achievement to edit
     *
     * @return Response
     */
    #[Route("/edit/{id}", name:"edit")]
    public function editCity(Request $request, Achievement $achievement): Response
    {
        $form = $this->createForm(AchievementType::class, $achievement);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $achievement->setUpdatedAt(new \DateTimeImmutable());
            $this->em->persist($achievement);
            $this->em->flush();

            $this->addFlash("success", "Le succès a bien été modifié.");
            return $this->redirectToRoute("admin_achievements_list");
        }
        return $this->render("achievement/add.html.twig", ["form" => $form->createView()]);
    }

    /**
     * Delete an achievement
     *
     * @param Request $request request's object
     * @param Achievement $achievement achievement to delete
     *
     * @return Response
     */
    #[Route("/delete/{id}", name:"delete")]
    public function deleteCity(Request $request, Achievement $achievement): Response
    {
        $this->em->remove($achievement);
        $this->em->flush();

        return $this->redirectToRoute("admin_achievements_list");
    }
}
