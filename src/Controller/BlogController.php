<?php

namespace App\Controller;

use App\Entity\BlogPost;
use App\Repository\BlogPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Attribute\Route;

class BlogController extends AbstractController
{

    /**
     * @var BlogPostRepository
     */
    private $blogPostRepository;
    /**
     * @var EntityManagerInterface
     */
    private $objectManager;

    public function __construct(BlogPostRepository $blogPostRepository, EntityManagerInterface $objectManager)
    {
        $this->blogPostRepository = $blogPostRepository;
        $this->objectManager = $objectManager;
    }

    #[Route("/blog", name: "blog")]
    public function showPosts(Security $security, Request $request)
    {
        $user = $security->getUser();
        $blogPostsAll = $this->blogPostRepository->findAll();
        return $this->render('blog/index.html.twig', [
            'blog_posts_all' => $blogPostsAll,
            'user' => $user,
            'full_name' => $request->getSession()->get("full_name")
        ]);
    }

    #[Route("/newPost", name: "postblog", methods: ["POST"])]
    public function addPost(Request $request, Security $security) {
        $user = $security->getUser();
        $dateTime = new \DateTime();
        $blogPost = new BlogPost();
        $blogPost->setTitle($request->request->get("title"));
        $blogPost->setDescription($request->request->get("description"));
        $blogPost->setBody($request->request->get("body"));
        $blogPost->setAuthor($user);
        $blogPost->setCreateAt($dateTime);
        $blogPost->setUpdatedAt($dateTime);
        $blogPost->setSlug("slug");
        $this->objectManager->persist($blogPost);
        $this->objectManager->flush();
        return $this->showPosts($security, $request);
    }
}
