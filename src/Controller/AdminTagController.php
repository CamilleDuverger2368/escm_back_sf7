<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Form\TagType;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/admin/tags", name: "admin_tags_")]
#[IsGranted("ROLE_ADMIN")]
class AdminTagController extends AbstractController
{
    private TagRepository $tagRep;
    private EntityManagerInterface $em;

    public function __construct(
        TagRepository $tagRep,
        EntityManagerInterface $em
    ) {
        $this->tagRep = $tagRep;
        $this->em = $em;
    }


    /**
     * List all tags
     *
     * @return Response
     */
    #[Route("", name:"list")]
    public function listTags(): Response
    {
        $tags = $this->tagRep->findAll();

        return $this->render("tag/index.html.twig", ["tags" => $tags]);
    }


    /**
     * Add a tag
     *
     * @param Request $request request's object
     *
     * @return Response
     */
    #[Route("/add", name:"add")]
    public function addTag(Request $request): Response
    {
        $tag = new Tag();
        $form = $this->createForm(TagType::class, $tag);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($tag);
            $this->em->flush();

            $this->addFlash("success", "Le tag a bien été ajouté.");
            return $this->redirectToRoute("admin_tags_list");
        }
        return $this->render("tag/add.html.twig", ["form" => $form->createView()]);
    }


    /**
     * Edit a tag
     *
     * @param Request $request request's object
     * @param Tag $tag tag to edit
     *
     * @return Response
     */
    #[Route("/edit/{id}", name:"edit")]
    public function editTag(Request $request, Tag $tag): Response
    {
        $form = $this->createForm(TagType::class, $tag);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($tag);
            $this->em->flush();

            $this->addFlash("success", "Le tag a bien été modifié.");
            return $this->redirectToRoute("admin_tags_list");
        }
        return $this->render("tag/add.html.twig", ["form" => $form->createView()]);
    }


    /**
     * Delete a tag
     *
     * @param Request $request request's object
     * @param Tag $tag tag to delete
     *
     * @return Response
     */
    #[Route("/delete/{id}", name:"delete")]
    public function deleteTag(Request $request, Tag $tag): Response
    {
        $this->em->remove($tag);
        $this->em->flush();

        return $this->redirectToRoute("admin_tags_list");
    }
}
