<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
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
    private $em;
    private $paginator;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->em = $em;
        $this->paginator = $paginator;
    }

    /**
     * Lists all post entities.
     *
     * @Route("/", name="root")
     * @Method("GET")
     */
    public function index(Request $request)
    {
        $query = $this->em->getRepository('App:Post')->createQueryBuilderWithCategory()
            ->getQuery();

        $posts = $this->paginator->paginate(
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

        $post = $this->em->getRepository(Post::class)->createQueryBuilderWithUserAndCategory()
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
            $this->em->persist($comment);
            $this->em->flush();
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
        $query = $this->em->getRepository('App:Post')->createQueryBuilderWithCategory()
            ->where("p.user = :user")
            ->setParameter("user", $user)
            ->getQuery();

        $posts = $this->paginator->paginate(
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
        $query = $this->em->getRepository('App:Post')->createQueryBuilderWithUser()
            ->where("p.category = :category")
            ->setParameter("category", $category)
            ->getQuery();

        $posts = $this->paginator->paginate(
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
        $categories = $this->em->getRepository('App:Category')->findAll();
        $posts = $this->em->getRepository('App:Post')->findBy(
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
