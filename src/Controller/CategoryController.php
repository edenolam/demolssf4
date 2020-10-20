<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    /**
     * @Route("/admin/category", name="category")
     * @param CategoryRepository $repository
     * @return Response
     */
    public function index(CategoryRepository $repository)
    {
        return $this->render('admin/category/index.html.twig', [
            'categories' => $repository->findAll(),
        ]);
    }

    /**
     * @Route("/admin/category/new", name="category_create")
     * @Route("/admin/category/{id}/edit", name="category_edit")
     * @param Category|null $category
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function form(Category $category = null, Request $request, EntityManagerInterface $manager)
    {
        if (!$category){
            $category = new Category();
        }
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $manager->persist($category);
            $manager->flush();
            return $this->redirectToRoute('category');
        }
        return $this->render('admin/category/create.html.twig', [
            'form' => $form->createView(),
            'editMode' => $category->getId() !== null
        ]);
    }



}
