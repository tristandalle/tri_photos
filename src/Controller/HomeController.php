<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PhotoRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home_welcome")
     */
    public function welcome()
    {
        return $this->render('home/welcome.html.twig');
    }

    /**
     * @Route("/home", name="home_connected")
     */
    public function home(UserRepository $userRepository)
    {
        $user =$userRepository->findAll();

        return $this->render('home/home.html.twig', [
            "user" => $user
        ]);
    }
}
