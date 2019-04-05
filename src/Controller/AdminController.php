<?php

namespace App\Controller;

use App\Repository\AlbumRepository;
use App\Repository\PhotoRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin_home")
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
     * @Route("/admin/members", name="admin_members")
     */
    public function adminMembersAction(UserRepository $userRepository)
    {
        return $this->render('admin/adminMembersView.html.twig', [
            'users' => $userRepository->findAll()
        ]);
    }

    /**
     * @Route("/admin/remove/member/{id}", name="admin_remove_member")
     */
    public function removeMembersAction($id, ObjectManager $manager, UserRepository $userRepository)
    {
        $userToRemove = $userRepository->find($id);
        $manager->remove($userToRemove);
        $manager->flush();
        return $this->redirectToRoute('admin_members');
    }

    /**
     * @Route("/admin/photos", name="admin_photos")
     */
    public function adminPhotosAction(PhotoRepository $photoRepository)
    {
        return $this->render('admin/adminPhotosView.html.twig', [
            'photos' => $photoRepository->findAll()
        ]);
    }

    /**
     * @Route("/admin/remove/photo/{id}", name="admin_remove_photo")
     */
    public function removePhotoAction($id, ObjectManager $manager, PhotoRepository $photoRepository)
    {
        $photoToRemove = $photoRepository->find($id);
        $manager->remove($photoToRemove);
        $manager->flush();
        return $this->redirectToRoute('admin_photos');
    }
}
