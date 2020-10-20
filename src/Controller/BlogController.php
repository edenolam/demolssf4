<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\CategoryType;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    /**
     * @Route("/blog", name="blog")
     * @param ArticleRepository $articleRepository
     * @return Response
     */
    public function index(ArticleRepository $articleRepository)
    {
        $articles = $articleRepository->findAll();
        return $this->render('blog/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    /**
     * @Route ("/", name="home")
     */
    public function home()
    {
        return $this->render('blog/home.html.twig');
    }

    /**
     * @Route ("/blog/new", name="blog_create")
     * @Route ("/blog/{id}/edit", name="blog_edit")
     * @param Article|null $article
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function form(Article $article = null, Request $request, EntityManagerInterface $manager)
    {
        if (!$article){
            $article = new Article();
        }
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            if (!$article->getId()){
                $article->setCreatedAt(new \DateTime());
            }
            $manager->persist($article);
            $manager->flush();
            return $this->redirectToRoute('blog_show', ['id' => $article->getId()]);
        }
        return $this->render('blog/create.html.twig', [
            'form' => $form->createView(),
            'editMode' => $article->getId() !== null
        ]);
    }

    /**
     * @Route ("/blog/{id}", name="blog_show")
     * @param Article $article
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function show(Article $article, Request $request, EntityManagerInterface $manager)
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $comment->setCreatedAt(new \DateTime());
            $comment->setArticle($article);
            $manager->persist($comment);
            $manager->flush();
            return $this->redirectToRoute('blog_show', ['id' => $article->getId()]);
        }
        // @ParamConverter annotation
        return $this->render('blog/show.html.twig', [
            'article' => $article,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/new_category", name="create_category")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function createCategory(Request $request, EntityManagerInterface $manager)
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $manager->persist($category);
            $manager->flush();
            return $this->redirectToRoute('blog_create');
        }
        return $this->render('admin/category/create.html.twig', [

        ]);
    }




}
