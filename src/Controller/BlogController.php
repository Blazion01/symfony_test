<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class BlogController extends AbstractController
{
    /** @var integer */
    const POST_LIMIT = 5 ;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var \Doctrine\Common\Persistence\ObjectRepository */
    private $authorRepository;

    /** @var \Doctrine\Common\Persistence\ObjectRepository */
    private $blogPostRepository;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->blogPostRepository = $entityManager->getRepository('App:BlogPost');
        $this->authorRepository = $entityManager->getRepository('App:Author');
    }

    /**
     * @Route("/", name="homepage")
     * @Route("/entries", name="entries")
     */
    public function indexAction(): Response
    {
        $blogPosts = $this->blogPostRepository->findAll();
        $totalBlogPosts =$this->blogPostRepository->getPostCount();
        $entryLimit = self::POST_LIMIT;
        if(isset($_GET['page'])) {
            $page = $_GET['page'];
        } else {$page = 1;}
        $lastPage = floor($totalBlogPosts / $entryLimit) + 1;

        return $this->render('blog/entries.html.twig', [
            'blogPosts' => $blogPosts,
            'totalBlogPosts' => $totalBlogPosts,
            'page' => $page,
            'lastPage' => $lastPage,
            'entries' => ($page - 1) * $entryLimit,
            'entryLimit' => $entryLimit,
            'user' => $this->getUser()
        ]);
    }

    /**
     * @Route("/entry/{slug}", name="entry")
     */
    public function entryAction($slug)
    {
        $blogPost = $this->blogPostRepository->findOneBySlug($slug);

        if (!$blogPost) {
            $this->addFlash('error', 'Unable to find entry!');

            return $this->redirectToRoute('entries');
        }

        return $this->render('blog/entry.html.twig', array(
            'blogPost' => $blogPost
        ));
    }

    /**
     * @Route("/author/{name}", name="author")
     */
    public function authorAction($name)
    {
        $author = $this->authorRepository->findOneByUsername($name);

        if (!$author) {
            $this->addFlash('error', 'Unable to find author!');
            return $this->redirectToRoute('entries');
        }

        return $this->render('blog/author.html.twig', [
            'author' => $author
        ]);
    }
}
