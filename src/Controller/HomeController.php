<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
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

    /**
     * @Route("/albums", name="home_albums")
     */
    public function showAlbumsAction()
    {
        return $this->render('home/albums.html.twig');
    }

    /**
     * @Route("/album", name="home_one_album")
     */
    public function showOneAlbumAction()
    {
        return $this->render('home/one-album.html.twig');
    }
}
