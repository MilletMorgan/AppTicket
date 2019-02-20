<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\LoginType;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private $repository;

    private $passwordEncoder;

    public function __construct(UserRepository $repository, ObjectManager $em, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->repository = $repository;
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/user", name="user")
     */
    public function index(SessionInterface $session)
    {
        $session = New Session();
        $id = $session->get('id');

        if(!$session->has('email')) $session->set('email', array());

        return $this->render('index.html.twig', [
            'id'        => $id,
            'mainNavLogin' => true, 'title' => 'Connexion',
            'mainNavRegistration' => true,
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     * @Route("/register", name="register")
     */
    public function register(Request $request, SessionInterface $session, UserPasswordEncoderInterface $passwordEncoder)
    {
        $session = new Session();

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $email = $user->getEmail();
            $id = $user->getId();
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $user->setIsActive(true);
            $user->addRole("ROLE_ADMIN");
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Votre compte à bien été enregistré.');

            $session->set('email', $email);
            $session->set('id', $id);
        }

        return $this->render('user/new.html.twig', [
            'mainNavLogin' => true, 'title' => 'Inscription',
            'form' => $form->createView(),
            'mainNavRegistration' => true,
        ]);
    }

    /**
     * @Route("/login", name="login")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils)
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $form = $this->get('form.factory')
            ->createNamedBuilder(null)
            ->add('_username', null, ['label' => 'Email'])
            ->add('_password', PasswordType::class, ['label' => 'Mot de passe'])
            ->add('ok', SubmitType::class, ['label' => 'Login', 'attr' => ['class' => 'btn-primary btn-block']])
            ->getForm();

        return $this->render('security/login.html.twig', [
            'mainNavLogin' => true, 'title' => 'Connexion',
            'form' => $form->createView(),
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(SessionInterface $session)
    {
        $session = New Session();
        $session->clear();

        return new Response('Logout successful');
    }

    /**
     * @Route("/user/{email}", name="user_show")
     */
    public function show($email)
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($email);

        if (!$user) {
            throw $this->createNotFoundException(
                'No user found for email '.$email
            );
        }

        return $this->render('user/show.html.twig', [
            'id'        => $user->getId(),
            'email'     => $user->getEmail(),
            'password'  => $user->getPassword()
        ]);
    }

    /**
     * @Route("/admin", name="admin")
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
     * @Route("/admin/delete/{id}", name="admin_delete")
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
     * @Route("/admin/edit/{id}", name="admin_edit")
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
