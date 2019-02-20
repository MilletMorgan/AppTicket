<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;
use App\Form\UserType;

/** @Route("/admin") */
class AdminController extends Controller
{

    /**
     * @Route("/users", name="users")
     */
    public function showAllUser()
    {
        $users = $this->getDoctrine()
            ->getRepository(User::class)
            ->findAll();

        if (!$users) {
            throw $this->createNotFoundException(
                'No event found'
            );
        }

        return $this->render('security/showAllUser.html.twig', array('users' => $users));
    }

    /**
     * @Route("/delete/{id}", name="admin_delete")
     */
    public function delete($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->findOneById($id);

        if(!$user){
            throw $this->createNotFoundException('No user found for id '.$id);
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return $this->render('index.html.twig');
    }

    /**
     * @Route("/edit/{id}", name="admin_edit")
     */
    public function edit($id, Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->findOneById($id);

        if(!$user)
            throw $this->createNotFoundException('No user found for id '.$id);
        else {
            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()) {
                $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($password);
                $user->setIsActive(true);
                $user->getRoles("ROLE_ADMIN");
                $entityManager->flush();
                $this->addFlash('success', 'Votre compte à bien été enregistré.');
            }

            return $this->render('security/edit.html.twig', [
                'mainNavLogin' => true, 'title' => 'Inscription',
                'form' => $form->createView(),
                'mainNavRegistration' => true,
            ]);
        }
    }
}