<?php

namespace App\Controller;



use App\Entity\Groupe;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\PropertyRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class UserController extends AbstractController
{

    /**
     * @var UserRepository
     */

    private $repository;
    /**
     * @var ObjectManager
     */
    private ObjectManager $em;

    public function __construct(UserRepository $repository, EntityManagerInterface $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }

    /**
     * @Route("/" , name="index")
     * @return Response
     */
    public function index(ManagerRegistry $doctrine): Response
    {

        return $this->render('user/index.html.twig');
    }

    /**
     * @Route("/create")
     * @param Request $request
     * @param $slugger
     */
    public function createUser(Request $request, SluggerInterface $slugger)
    {
        $form = $this->createForm(UserType::class, new User());
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $image = $form->get('image')->getData();
            if ($image) {
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();

                try {
                    $image->move(
                        $this->getParameter('image_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Image cannot be saved.');
                }
                $user->setImage($newFilename);
            }

            $this->em->persist($user);
            $this->em->flush();
            $this->addFlash('success', 'User was created!');


        }

        return $this->render('user/create.html.twig', [
            'form' => $form->createView()
        ]);

    }
}