<?php

namespace App\Controller;

use App\Entity\City;
use App\Form\CityType;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/admin/cities", name: "admin_cities_")]
#[IsGranted("ROLE_ADMIN")]
class AdminCityController extends AbstractController
{
    private CityRepository $cityRep;
    private EntityManagerInterface $em;

    public function __construct(
        CityRepository $cityRep,
        EntityManagerInterface $em
    ) {
        $this->cityRep = $cityRep;
        $this->em = $em;
    }

    /**
     * List all cities
     *
     * @return Response
     */
    #[Route("", name:"list")]
    public function listCities(): Response
    {
        $cities = $this->cityRep->findAll();

        return $this->render("city/index.html.twig", ["cities" => $cities]);
    }

    /**
     * Add a city
     *
     * @return Response
     */
    #[Route("/add", name:"add")]
    public function addCity(Request $request): Response
    {
        $city = new City();
        $form = $this->createForm(CityType::class, $city);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($city);
            $this->em->flush();

            $this->addFlash("success", "La ville a bien été ajoutée.");
            return $this->redirectToRoute("admin_cities_list");
        }
        return $this->render("city/add.html.twig", ["form" => $form->createView()]);
    }

    /**
     * Edit a city
     *
     * @param Request $request request's object
     * @param City $city city to edit
     *
     * @return Response
     */
    #[Route("/edit/{id}", name:"edit")]
    public function editCity(Request $request, City $city): Response
    {
        $form = $this->createForm(CityType::class, $city);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($city);
            $this->em->flush();

            $this->addFlash("success", "La ville a bien été modifiée.");
            return $this->redirectToRoute("admin_cities_list");
        }
        return $this->render("city/add.html.twig", ["form" => $form->createView()]);
    }

    /**
     * Delete a city
     *
     * @param Request $request request's object
     * @param City $city city to delete
     *
     * @return Response
     */
    #[Route("/delete/{id}", name:"delete")]
    public function deleteCity(Request $request, City $city): Response
    {
        $this->em->remove($city);
        $this->em->flush();

        return $this->redirectToRoute("admin_cities_list");
    }
}
