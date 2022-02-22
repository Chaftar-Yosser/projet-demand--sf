<?php

namespace App\Controller;



use App\Entity\Groupe;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\PropertyRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Class UserController
 * @package App\Controller
 * @Route("/user")
 */
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
     * @Route("/" , name="user_index")
     * @return Response
     */
    public function index(): Response
    {

        $userList = $this->repository->findAll();
        return $this->render('user/index.html.twig', ["user_list" => $userList]);
    }

    /**
     * @Route("/create", name="user_create")
     * @param Request $request
     * @param $slugger
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
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
            return $this->redirectToRoute('user_index');

        }
        return $this->render('user/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/edit/{id}", name="user_edit")
     * @param User $user
     * @param Request $request
     * @return Response
     */
    public function edit(User $user , Request $request, SluggerInterface $slugger)
    {

        $form = $this->createForm(UserType::class, $user);
        $originalImage = $user->getImage();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

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
                    //delete old image
                    if($originalImage)
                        unlink($this->getParameter('image_directory')."/".$originalImage);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Image cannot be saved.');
                }
                $user->setImage($newFilename);
            }

            $this->em->flush();
            $this->addFlash('success' , 'Image modifié avec succés');
            return $this->redirectToRoute('user_index');

        }

        return $this->render('user/edit.html.twig', [
            'user' =>$user,
            'form' =>$form->createView()
        ]);
    }

    /**
     * @Route("/delete/{id}", name="user_delete")
     * @param User $user
     *
     */
    public function delete(User $user , Request $request)
    {
       // if ($this->isCsrfTokenValid('delete' . $user->getId() , $request->get('_token'))) {
            $this->em->remove($user);
            $this->em->flush();
            $this->addFlash('success' , 'user supprimé avec succés');
       // }
        return $this->redirectToRoute('user_index');
    }


}