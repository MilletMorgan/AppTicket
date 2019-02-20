<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;

class MemberController extends Controller\AbstractController
{
    /**
     * @route("/")
     */
    public function index()
    {
        return $this->render('member/index.html.twig', ['mainNavMember'=>true, 'title'=>'Espace Membre']);
    }

    /**
     * @Route("/{id}", name="user_show")
     */
    public function show($id)
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($id);

        if (!$user) {
            throw $this->createNotFoundException(
                'No user found for email '.$id
            );
        }

        return $this->render('user/show.html.twig', [
            'id'        => $user->getId(),
            'email'     => $user->getEmail(),
            'password'  => $user->getPassword()
        ]);
    }
}