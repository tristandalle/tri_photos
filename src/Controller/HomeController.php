<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home(UserRepository $userRepository)
    {
        $user = $userRepository->findAll();
        return $this->render('home/home.html.twig', [
            "user" => $user
        ]);
    }

    /**
     * @Route("/under-construction", name="home_construction")
     */
    public function construction()
    {
        return $this->render('home/under-construction.html.twig');
    }
}
