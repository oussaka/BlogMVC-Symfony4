<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Post controller.
 *
 * @Route("post", name="")
 */
class PostController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Lists all post entities.
     *
     * @Route("/", name="admin_post_index")
     * @Method("GET")
     */
    public function index(Request $request)
    {
        $repository = $this->em->getRepository(Post::class);
        $paginator  = $this->get('knp_paginator');

        $query = $repository->createQueryBuilderWithCategory()
            ->orderBy('p.createdAt', 'desc')
            ->getQuery();

        $posts = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        $deleteForms = [];
        foreach($posts as $post) {
            $deleteForms[$post->getId()] = $this->createDeleteForm($post)->createView();
        }

        return $this->render('admin/post/index.html.twig', array(
            'posts' => $posts,
            'delete_forms' => $deleteForms
        ));
    }

    /**
     * Creates a new post entity.
     *
     * @Route("/new", name="admin_post_new")
     * @Method({"GET", "POST"})
     */
    public function new(Request $request)
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post, [
            'action' => $this->generateUrl('admin_post_new')
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($post);
            $this->em->flush();
            $this->addFlash('success', 'Post created successfully');
            return $this->redirectToRoute('admin_post_index');
        }

        return $this->render('admin/post/new.html.twig', array(
            'post' => $post,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing post entity.
     *
     * @Route("/{id}/edit", name="admin_post_edit")
     * @Method({"GET", "POST"})
     */
    public function edit(Request $request, Post $post)
    {
        $editForm = $this->createForm(PostType::class, $post, [
            'action' => $this->generateUrl('admin_post_edit', ['id' => $post->getId()])
        ]);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Post edited successfully');
            return $this->redirectToRoute('admin_post_edit', array('id' => $post->getId()));
        }

        return $this->render('admin/post/edit.html.twig', array(
            'post' => $post,
            'edit_form' => $editForm->createView()
        ));
    }

    /**
     * Deletes a post entity.
     *
     * @Route("/{id}", name="admin_post_delete")
     * @Method("DELETE")
     */
    public function delete(Request $request, Post $post)
    {
        $form = $this->createDeleteForm($post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->remove($post);
            $this->addFlash('success', 'Post deleted');
            $this->em->flush($post);
        }

        return $this->redirectToRoute('admin_post_index');
    }

    /**
     * Creates a form to delete a post entity.
     *
     * @param Post $post The post entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Post $post)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_post_delete', array('id' => $post->getId())))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }
}
