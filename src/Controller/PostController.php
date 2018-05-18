<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Form\CommentType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Post controller.
 *
 */
class PostController extends AbstractController
{
    /**
     * Lists all post entities.
     *
     * @Route("/", name="root")
     * @Method("GET")
     */
    public function index(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getRepository('App:Post')->createQueryBuilderWithCategory()
            ->getQuery();

        $posts = $this->get('knp_paginator')->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('post/index.html.twig', array(
            'posts' => $posts,
            'page' => $request->query->getInt('page', 1)));
    }

    /**
     * Finds and displays a post entity.
     *
     * @Route("/post/{slug}", name="post_show")
     * @Method({"GET", "POST"})
     */
    public function show(Request $request, string $slug): Response
    {

        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository(Post::class)->createQueryBuilderWithUserAndCategory()
            ->where('p.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getSingleResult();

        $comment = new Comment();
        $comment->setPost($post);

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setPost($post);
            $em->persist($comment);
            $em->flush();
            $this->addFlash('success', 'Thanks for your comment');
            return $this->redirectToRoute('post_show', ['slug' => $post->getSlug()]);
        }

        return $this->render('post/show.html.twig', [
            'comment_form' => $form->createView(),
            'post' => $post
        ]);
    }

    /**
     * @Route("/author/{id}", name="post_author")
     * @Method("GET")
     */
    public function author(Request $request, User $user)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getRepository('App:Post')->createQueryBuilderWithCategory()
            ->where("p.user = :user")
            ->setParameter("user", $user)
            ->getQuery();

        $posts = $this->get('knp_paginator')->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('post/index.html.twig', [
            'author' => $user,
            'posts' => $posts,
            'page' => $request->query->getInt('page', 1)
        ]);
    }

    /**
     * @Route("/category/{slug}", name="post_category")
     * @Method("GET")
     */
    public function category(Request $request, Category $category)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getRepository('App:Post')->createQueryBuilderWithUser()
            ->where("p.category = :category")
            ->setParameter("category", $category)
            ->getQuery();

        $posts = $this->get('knp_paginator')->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('post/index.html.twig', [
            'category' => $category,
            'posts' => $posts,
            'page' => $request->query->getInt('page', 1)
        ]);
    }

    public function sidebar()
    {
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository('App:Category')->findAll();
        $posts = $em->getRepository('App:Post')->findBy(
            [],
            ['createdAt' => 'DESC'],
            2,
            0
        );
        return $this->render('partials/sidebar.html.twig', [
            'categories' => $categories,
            'posts' => $posts
        ]);
    }

}
