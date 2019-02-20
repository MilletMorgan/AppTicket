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
     * @Route("/", name="user")
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

        return $this->render('index.html.twig');
    }
}
