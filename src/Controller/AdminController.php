<?php

namespace App\Controller;

use App\Entity\Photo;
use App\Entity\User;
use App\Repository\AlbumRepository;
use App\Repository\PhotoRepository;
use App\Repository\UserRepository;
use App\Service\Paginator;
use Doctrine\Common\Persistence\ObjectManager;
use Swift_Mailer;
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
     * @Route("/admin/members/{page<\d+>?1}", name="admin_members")
     */
    public function adminMembersAction($page, Paginator $paginator)
    {
        $paginator->setEntityClass(User::class)
            ->setCurrentPage($page)
            ->setLimit(5);

        return $this->render('admin/adminMembersView.html.twig', [
            'users' => $paginator->getData(),
            'pages' => $paginator->getPages(),
            'page' => $page
        ]);
    }

    /**
     * @Route("/admin/remove/member/{id}", name="admin_remove_member")
     */
    public function removeMembersAction($id, ObjectManager $manager, UserRepository $userRepository, Swift_Mailer $mailer)
    {
        $userToRemove = $userRepository->find($id);

        $userName = $userToRemove->getUsername();
        $userEmail = $userToRemove->getEmail();

        $message = (new \Swift_Message('RIP Votre compte Triphotos '))
            ->setFrom('triphoto.contact@gmail.com')
            ->setTo($userEmail)
            ->setBody(
                $this->renderView(
                    'emails/remove-member.html.twig',
                    ['name' => $userName]
                ),
                'text/html'
            );

        $mailer->send($message);

        $manager->remove($userToRemove);
        $manager->flush();
        return $this->redirectToRoute('admin_members');
    }

    /**
     * @Route("/admin/photos/{page<\d+>?1}", name="admin_photos")
     */
    public function adminPhotosAction($page, Paginator $paginator)
    {
        $paginator->setEntityClass(Photo::class)
            ->setCurrentPage($page);

        return $this->render('admin/adminPhotosView.html.twig', [
            'photos' => $paginator->getData(),
            'pages' => $paginator->getPages(),
            'page' => $page
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
