<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Annotation;
use App\Entity\User;
use App\Repository\UserRepository;


class AppController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(SessionInterface $session)
    {
        return $this->render('index.html.twig');
    }
}