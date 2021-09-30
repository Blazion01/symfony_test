<?php

namespace App\Controller;

function console_log($output, $with_script_tags = true) {
    $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) . 
');';
    if ($with_script_tags) {
        $js_code = '<script>' . $js_code . '</script>';
    }
    echo $js_code;
}

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Author;
use App\Form\AuthorFormType;
use App\Entity\BlogPost;
use App\Form\EntryFormType;

/**
 * @Route("/admin")
 */

class AdminController extends AbstractController
{
    /** @var EntityManagerInterface */
    private $entityManager;
    
    /** @var \Doctrine\Persistence\ObjectRepository */
    private $authorRepository;
    
    /** @var \Doctrine\Persistence\ObjectRepository */
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
     * @Route("/create-entry", name="admin_create_entry")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createEntryAction(Request $request)
    {
        if($this->getUser()) {
            $blogPost = new BlogPost();

            $author = $this->authorRepository->findOneBy(["username" => $this->getUser()->getUserName()]);
            $blogPost->setAuthor($author);

            $form = $this->createForm(EntryFormType::class, $blogPost);
            $form->handleRequest($request);

            // Check is valid
            if ($form->isSubmitted() && $form->isValid()) {
                $this->entityManager->persist($blogPost);
                $this->entityManager->flush($blogPost);

                $this->addFlash('success', 'Congratulations! Your post is created');

                return $this->redirectToRoute('admin_entries');
            }

            return $this->render('admin/entry_form.html.twig', [
                'form' => $form->createView()
            ]);
        } else { return $this->redirectToRoute('homepage'); }
    }

    /**
     * @Route("/delete-entry/{entryId}", name="admin_delete_entry")
     *
     * @param $entryId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteEntryAction($entryId)
    {
        $blogPost = $this->blogPostRepository->findOneBy(["id" => $entryId]);
        $author = $this->authorRepository->findOneBy(["username" => $this->getUser()->getUserName()]);

        if (!$blogPost || $author !== $blogPost->getAuthor()) {
            $this->addFlash('error', 'Unable to remove entry!');

            return $this->redirectToRoute('admin_entries');
        }

        $this->entityManager->remove($blogPost);
        $this->entityManager->flush();

        $this->addFlash('success', 'Entry was deleted!');

        return $this->redirectToRoute('admin_entries');
    }

    /**
     * @Route("/author/create", name="author_create")
     */
    public function createAuthorAction(Request $request)
    {
        if ($this->getUser()){
        //    console_log($this);
            if ($this->authorRepository->findOneBy(["username" => $this->getUser()->getUserName()])) {
                // Redirect to dashboard.
                $this->addFlash('error', 'Unable to create author, author already exists!');

                return $this->redirectToRoute('homepage');
            }

            $author = new Author();
            $author->setUsername($this->getUser()->getUserName());

            $form = $this->createForm(AuthorFormType::class, $author);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->entityManager->persist($author);
                $this->entityManager->flush($author);

                $request->getSession()->set('user_is_author', true);
                $this->addFlash('success', 'Congratulations! You are now an author.');

                return $this->redirectToRoute('homepage');
            }

            return $this->render('admin/author_create.html.twig', [
                'form' => $form->createView()
            ]);
        } else { return $this->redirectToRoute('homepage'); }
    }
}
