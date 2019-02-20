<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/member") */
class MemberController extends Controller\AbstractController
{
    /**
     * @route("/")
     */
    public function index()
    {
        return $this->render('member/index.html.twig', ['mainNavMember'=>true, 'title'=>'Espace Membre']);
    }
}