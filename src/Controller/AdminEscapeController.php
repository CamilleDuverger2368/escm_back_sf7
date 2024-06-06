<?php

namespace App\Controller;

use App\Entity\Description;
use App\Entity\Escape;
use App\Entity\Link;
use App\Form\DescriptionType;
use App\Form\EscapeType;
use App\Form\FindEscapeType;
use App\Form\LinkType;
use App\Repository\CityRepository;
use App\Repository\EscapeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/admin/escapes", name: "admin_escapes_")]
#[IsGranted("ROLE_ADMIN")]
class AdminEscapeController extends AbstractController
{
    private EscapeRepository $escapeRep;
    private CityRepository $cityRep;
    private EntityManagerInterface $em;

    public function __construct(
        EscapeRepository $escapeRep,
        CityRepository $cityRep,
        EntityManagerInterface $em
    ) {
        $this->escapeRep = $escapeRep;
        $this->cityRep = $cityRep;
        $this->em = $em;
    }


    /**
     * List all escapes
     *
     * @param Request $request request's object
     *
     * @return Response
     */
    #[Route("", name:"list")]
    public function listEscapes(Request $request): Response
    {
        $form = $this->createForm(FindEscapeType::class);
        $form->handleRequest($request);

        $escapes = null;

        if ($form->isSubmitted()) {
            $escapes = $this->escapeRep->getEscapesByEntrepriseAndCityAndName($form->getData());
        }

        if ($escapes === null) {
            $escapes = $this->escapeRep->findAll();
        }

        return $this->render("escape/index.html.twig", ["form" => $form->createView(), "escapes" => $escapes]);
    }


    /**
     * Details one escape
     *
     * @param Request $request request's object
     * @param Escape $escape escape to detail
     *
     * @return Response
     */
    #[Route("/{id}", name:"one")]
    public function showEscape(Request $request, Escape $escape): Response
    {
        intval($request->query->get("city")) ? $idCity = intval($request->query->get("city")) : $idCity = null;

        $citySelected = $this->cityRep->findOneBy(["id" => $idCity]) ?? null;

        return $this->render("escape/details.html.twig", ["escape" => $escape, "citySelected" => $citySelected]);
    }

    /**
     * Create an escape
     *
     * @param Request $request request's object
     *
     * @return Response
     */
    #[Route("/informations/add", name:"informations_add")]
    public function addEscape(Request $request): Response
    {
        $escape = new Escape();
        $form = $this->createForm(EscapeType::class, $escape);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($escape);
            $this->em->flush();

            $this->addFlash("success", "L'escape a bien été ajouté.");
            return $this->redirectToRoute("admin_escapes_list");
        }
        return $this->render("escape/add.html.twig", ["form" => $form->createView()]);
    }

    /**
     * Edit basic informations
     *
     * @param Request $request request's object
     * @param Escape $escape escape to edit
     *
     * @return Response
     */
    #[Route("/informations/edit/{id}", name:"informations_edit")]
    public function editEscape(Request $request, Escape $escape): Response
    {
        $form = $this->createForm(EscapeType::class, $escape);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($escape);
            $this->em->flush();

            $this->addFlash("success", "L'escape a bien été modifié.");
            return $this->redirectToRoute("admin_escapes_list");
        }
        return $this->render("escape/add.html.twig", ["form" => $form->createView()]);
    }

    /**
     * Delete escape
     *
     * @param Request $request request's object
     * @param Escape $escape escape to delete
     *
     * @return Response
     */
    #[Route("/informations/delete/{id}", name:"informations_delete")]
    public function deleteEscape(Request $request, Escape $escape): Response
    {
        $this->em->remove($escape);
        $this->em->flush();

        return $this->redirectToRoute("admin_escapes_list");
    }

    /**
     * Add description
     *
     * @param Request $request request's object
     * @param Escape $escape escape
     *
     * @return Response
     */
    #[Route("/description/add/{id}", name:"description_add")]
    public function addDescriptionToEscape(Request $request, Escape $escape): Response
    {
        $description = new Description();
        $description->setEscape($escape);
        $form = $this->createForm(DescriptionType::class, $description);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($description);
            $this->em->flush();

            $this->addFlash("success", "La description a bien été ajoutée.");
            return $this->redirectToRoute("admin_escapes_list");
        }
        return $this->render("escape/add-description.html.twig", ["form" => $form->createView(), "escape" => $escape]);
    }

    /**
     * Edit a description
     *
     * @param Request $request request's object
     * @param Description $description description to edit
     *
     * @return Response
     */
    #[Route("/description/edit/{id}", name:"description_edit")]
    public function editDescriptionFromEscape(Request $request, Description $description): Response
    {
        $form = $this->createForm(DescriptionType::class, $description);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($description);
            $this->em->flush();

            $this->addFlash("success", "La description a bien été modifiée.");
            return $this->redirectToRoute("admin_escapes_list");
        }
        return $this->render("escape/add-description.html.twig", [
            "form" => $form->createView(),
            "escape" => $description->getEscape()
        ]);
    }

    /**
     * Delete a description
     *
     * @param Request $request request's object
     * @param Description $description description to delete
     *
     * @return Response
     */
    #[Route("/description/delete/{id}", name:"description_delete")]
    public function deleteDescriptionFromEscape(Request $request, Description $description): Response
    {
        $this->em->remove($description);
        $this->em->flush();

        return $this->redirectToRoute("admin_escapes_list");
    }

    /**
     * Add a link
     *
     * @param Request $request request's object
     * @param Escape $escape escape
     *
     * @return Response
     */
    #[Route("/link/add/{id}", name:"link_add")]
    public function addLinkToEscape(Request $request, Escape $escape): Response
    {
        $link = new Link();
        $link->setEscape($escape);
        $form = $this->createForm(LinkType::class, $link);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($link);
            $this->em->flush();

            $this->addFlash("success", "Le lien a bien été ajouté.");
            return $this->redirectToRoute("admin_escapes_list");
        }
        return $this->render("escape/add-link.html.twig", ["form" => $form->createView(), "escape" => $escape]);
    }

    /**
     * Edit a link
     *
     * @param Request $request request's object
     * @param Link $link link to edit
     *
     * @return Response
     */
    #[Route("/link/edit/{id}", name:"link_edit")]
    public function editLinkFromEscape(Request $request, Link $link): Response
    {
        $form = $this->createForm(LinkType::class, $link);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($link);
            $this->em->flush();

            $this->addFlash("success", "Le lien a bien été modifié.");
            return $this->redirectToRoute("admin_escapes_list");
        }
        return $this->render("escape/add-link.html.twig", [
            "form" => $form->createView(),
            "escape" => $link->getEscape()
        ]);
    }

    /**
     * Delete a link
     *
     * @param Request $request request's object
     * @param Link $link link to delete
     *
     * @return Response
     */
    #[Route("/link/delete/{id}", name:"link_delete")]
    public function deleteLinkFromEscape(Request $request, Link $link): Response
    {
        $this->em->remove($link);
        $this->em->flush();

        return $this->redirectToRoute("admin_escapes_list");
    }
}
