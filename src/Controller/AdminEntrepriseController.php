<?php

namespace App\Controller;

use App\Entity\Entreprise;
use App\Form\EntrepriseType;
use App\Form\FindEntrepriseType;
use App\Form\FindEscapesOfType;
use App\Repository\CityRepository;
use App\Repository\EntrepriseRepository;
use App\Repository\EscapeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/admin/entreprises", name: "admin_entreprises_")]
class AdminEntrepriseController extends AbstractController
{
    private EntrepriseRepository $entrepriseRep;
    private EscapeRepository $escapeRep;
    private CityRepository $cityRep;
    private EntityManagerInterface $em;

    public function __construct(
        EntrepriseRepository $entrepriseRep,
        EntityManagerInterface $em,
        EscapeRepository $escapeRep,
        CityRepository $cityRep
    ) {
        $this->entrepriseRep = $entrepriseRep;
        $this->escapeRep = $escapeRep;
        $this->cityRep = $cityRep;
        $this->em = $em;
    }


    /**
     * List all entreprises
     *
     * @param Request $request request's object
     *
     * @return Response
     */
    #[Route("", name:"list")]
    public function listEntreprises(Request $request): Response
    {
        $form = $this->createForm(FindEntrepriseType::class);
        $form->handleRequest($request);

        $entreprises = null;

        if ($form->isSubmitted()) {
            $entreprises = $this->entrepriseRep->getEntreprisesByCityAndName($form->getData());
        }

        if ($entreprises === null) {
            $entreprises = $this->entrepriseRep->findAll();
        }
        return $this->render("entreprise/index.html.twig", [
            "form" => $form->createView(),
            "entreprises" => $entreprises
        ]);
    }


    /**
     * Add an entreprise
     *
     * @param Request $request request's object
     *
     * @return Response
     */
    #[Route("/add", name:"add")]
    public function addEntreprise(Request $request): Response
    {
        $entreprise = new Entreprise();
        $form = $this->createForm(EntrepriseType::class, $entreprise);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($entreprise);
            $this->em->flush();

            return $this->redirectToRoute("admin_entreprises_list");
        }
        return $this->render("entreprise/add.html.twig", ["form" => $form->createView()]);
    }

    /**
     * Show an entreprise
     *
     * @param Request $request request's object
     * @param Entreprise $entreprise entreprise to show
     *
     * @return Response
     */
    #[Route("/{id}", name:"one")]
    public function showEntreprise(Request $request, Entreprise $entreprise): Response
    {
        intval($request->query->get("city")) ? $idCity = intval($request->query->get("city")) : $idCity = null;

        $citySelected = $this->cityRep->findOneBy(["id" => $idCity]) ?? null;

        $form = $this->createForm(FindEscapesofType::class);
        $form->handleRequest($request);

        $escapes = null;

        if ($form->isSubmitted()) {
            $data = $form->getData();
            $escapes = $this->escapeRep->getEscapesOfEntrepriseByCity(
                $entreprise,
                $citySelected,
                $data["actuals"],
                $data["unplayables"]
            );
        }

        if ($citySelected && $escapes === null) {
            $escapes = $this->escapeRep->getEscapesOfEntrepriseByCity($entreprise, $citySelected, true, true);
        }

        if ($escapes === null) {
            $escapes = $entreprise->getEscapes();
        }

        return $this->render("entreprise/details.html.twig", [
            "form" => $form->createView(),
            "entreprise" => $entreprise,
            "escapes" => $escapes,
            "citySelected" => $citySelected
        ]);
    }

    /**
     * Edit an entreprise
     *
     * @param Request $request request's object
     * @param Entreprise $entreprise entreprise to edit
     *
     * @return Response
     */
    #[Route("/edit/{id}", name:"edit")]
    public function editEntreprise(Request $request, Entreprise $entreprise): Response
    {
        $form = $this->createForm(EntrepriseType::class, $entreprise);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($entreprise);
            $this->em->flush();

            return $this->redirectToRoute("admin_entreprises_list");
        }
        return $this->render("entreprise/add.html.twig", ["form" => $form->createView()]);
    }


    /**
     * Delete an entreprise
     *
     * @param Request $request request's object
     * @param Entreprise $entreprise entreprise to delete
     *
     * @return Response
     */
    #[Route("/delete/{id}", name:"delete")]
    public function deleteEntreprise(Request $request, Entreprise $entreprise): Response
    {
        $this->em->remove($entreprise);
        $this->em->flush();

        return $this->redirectToRoute("admin_entreprises_list");
    }
}
