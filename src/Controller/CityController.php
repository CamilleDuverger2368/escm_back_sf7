<?php

namespace App\Controller;

use App\Entity\City;
use App\Repository\CityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route("/api/cities", name: "app_cities_")]
class CityController extends AbstractController
{
    private CityRepository $cityRep;
    private SerializerInterface $serializer;

    public function __construct(
        CityRepository $cityRep,
        SerializerInterface $serializer
    ) {
        $this->cityRep = $cityRep;
        $this->serializer = $serializer;
    }

    /**
     * Return all cities
     *
     * @api GET
     *
     * @return JsonResponse
     */
    #[Route("", name:"list", methods: ["GET"])]
    public function getCities(): JsonResponse
    {
        $cities = $this->cityRep->findAll();
        $json = $this->serializer->serialize($cities, "json");

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }

    /**
     * Return one city
     *
     * @api GET
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name:"unique", methods: ["GET"])]
    public function getCity(City $city): JsonResponse
    {
        $json = $this->serializer->serialize($city, "json");

        return new JsonResponse($json, Response::HTTP_OK, ["accept" => "json"], true);
    }
}
