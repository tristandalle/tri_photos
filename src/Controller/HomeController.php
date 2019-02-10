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

    /**
     * @Route("/photos", name="home_all_photos")
     */
    public function showAllPhotosAction(PhotoRepository $photoRepository, UserRepository $userRepository, ObjectManager $manager)
    {
        $currentUser = $this->getUser();
        $photos = $photoRepository->findBy([
            'author' => $currentUser
        ]);

        return $this->render('home/all-photos.html.twig',[
            'photos' => $photos
        ]);
    }
}
