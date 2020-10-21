<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AdminController
 * @package App\Controller
 * @Route("/admin", name="admin_")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="index")
     */
    public function index()
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * @Route("/utilisateurs", name="utilisateurs")
     * @param UserRepository $repository
     * @return Response
     */
    public function usersList(UserRepository $repository)
    {
        return $this->render('admin/users.html.twig', [
            'users' => $repository->findAll(),
        ]);
    }

    /**
     * @Route("/utilisateur/{id}/modifier", name="userEdit")
     * @param User $user
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function userEdit(User $user, Request $request, EntityManagerInterface $manager)
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $form->getData();
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('message', 'Utilisateur modifié');
            return $this->redirectToRoute('admin_utilisateurs');
        }
        return $this->render('admin/edit_user.html.twig', [
            'form' => $form->createView(),
        ]);
    }


}
