<?php

namespace App\Controller;

use App\Repository\AlbumRepository;
use App\Repository\PhotoRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="home_admin")
     */
    public function homeAdminAction(UserRepository $userRepository, PhotoRepository $photoRepository, AlbumRepository $albumRepository)
    {
        return $this->render('admin/homeAdminView.html.twig', [
            'users' => $userRepository->findAll(),
            'photos' => $photoRepository->findAll(),
            'albums' => $albumRepository->findAll()
        ]);
    }

    /**
     * @Route("/admin/members", name="members_admin")
     */
    public function adminMembersAction(UserRepository $userRepository)
    {
        return $this->render('admin/adminMembersView.html.twig', [
            'users' => $userRepository->findAll()
        ]);
    }

    /**
     * @Route("/admin/photos", name="photos_admin")
     */
    public function adminPhotosAction(PhotoRepository $photoRepository)
    {
        return $this->render('admin/adminPhotosView.html.twig', [
            'photos' => $photoRepository->findAll()
        ]);
    }
}
