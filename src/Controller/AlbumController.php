<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Photo;
use App\Form\AlbumType;
use App\Repository\AlbumRepository;
use App\Repository\PhotoRepository;
use App\Service\Paginator;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class AlbumController extends AbstractController
{
    /**
     * @Route("/add/album", name="album_add")
     * @IsGranted("ROLE_USER")
     */
    public function addAlbumAction(Request $request, ObjectManager $manager)
    {
        $album = new Album();

        $form = $this->createForm(AlbumType::class, $album);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $album->setAuthor($this->getUser());
            $manager->persist($album);
            $manager->flush();

            return $this->redirectToRoute('album_all_albums');
        }

        return $this->render('album/add-album.html.twig', [
            'formAlbum' => $form->createView()
        ]);
    }

    /**
     * @Route("/albums", name="album_all_albums")
     * @IsGranted("ROLE_USER")
     */
    public function showAlbumsAction(AlbumRepository $albumRepository, PhotoRepository $photoRepository)
    {
        $currentUser = $this->getUser();
        $albums = $albumRepository->findBy([
            'author' => $currentUser
        ]);
        $photos = $photoRepository->findBy([
            'author' => $currentUser
        ]);

        return $this->render('album/all-albums.html.twig', [
            'albums' => $albums,
            'photos' => $photos
        ]);
    }

    /**
     * @Route("/album/{id<\d+>}/{filter<\d+>}/{page<\d+>?1}", defaults={"filter"=0}, name="album_one_album")
     * @Security("is_granted('ROLE_USER') and user === album.getAuthor()", message="Vous n'avez pas les droits d'accès pour cet album")
     */
    public function showOneAlbumAction(Album $album, $filter, $page, Paginator $paginator, AlbumRepository $albumRepository)
    {
        if (count($album->getPhotos()) == 0) {
            return $this->redirectToRoute('album_all_albums');
        }

        $paginator->setEntityClass(Photo::class)
            ->setCurrentPage($page)
            ->setLimit(20)
            ->setWhere(['album' => $album]);

        if ($filter != 0) {
            $photos = $albumRepository->find($album)->getPhotos();
        } else {
            $photos = $paginator->getData();
        }

        return $this->render('album/one-album.html.twig', [
            'total' => count($album->getPhotos()),
            'photos' => $photos,
            'pages' => $paginator->getPages(),
            'page' => $page,
            'album' => $album,
            'filter' => $filter
        ]);
    }

    /**
     * @Route("/album/{id<\d+>}/all/{photoId<\d+>}", name="album_one_album_all_photos")
     * @IsGranted("ROLE_USER")
     */
    public function showAllPhotosOfOneAlbumAction($id, $photoId, AlbumRepository $albumRepository)
    {
        $currentUser = $this->getUser();
        $albumToEdit = $albumRepository->find($id);
        $albumAuthor = $albumToEdit->getAuthor();
        if ($currentUser != $albumAuthor) {
            throw $this->createAccessDeniedException();
        }
        $authorAlbums = $albumToEdit->getAuthor()->getAlbums();
        if (count($albumToEdit->getPhotos()) == 0) {
            return $this->redirectToRoute('album_all_albums');
        }
        return $this->render('album/one-album-all-photos.html.twig', [
            'album' => $albumToEdit,
            'photoId' => $photoId,
            'albums' => $authorAlbums
        ]);
    }

    /**
     * @Route("/album/remove/{id}", name="album_remove_album")
     * @IsGranted("ROLE_USER")
     */
    public function removeAlbumAction($id, AlbumRepository $albumRepository, PhotoRepository $photoRepository, ObjectManager $manager)
    {
        $currentUser = $this->getUser();
        $albumToRemove = $albumRepository->find($id);
        $albumAuthor = $albumToRemove->getAuthor();
        if ($currentUser != $albumAuthor) {
            throw $this->createAccessDeniedException();
        }
        $photosFromAlbum = $photoRepository->findBy([
            'album' => $albumToRemove
        ]);

        foreach ($photosFromAlbum as $photoFromAlbum) {
            $photoFromAlbum->setAlbum(null);
        }
        $manager->remove($albumToRemove);
        $manager->flush();
        return $this->redirectToRoute('album_all_albums');
    }

    /**
     * @Route("/generate-token/{id}", name="album_generate_token")
     * @IsGranted("ROLE_USER")
     */
    public function generateTokenSharedAlbumAction($id, AlbumRepository $albumRepository, ObjectManager $manager, TokenGeneratorInterface $tokenGenerator)
    {
        $currentUser = $this->getUser();
        $sharedAlbum = $albumRepository->find($id);
        $albumAuthor = $sharedAlbum->getAuthor();
        if ($currentUser != $albumAuthor) {
            throw $this->createAccessDeniedException();
        }
        $token = $tokenGenerator->generateToken();
        $sharedAlbum->setAlbumToken($token);
        $manager->persist($sharedAlbum);
        $manager->flush();
        $this->addFlash(
            'success',
            'Vous pouvez maintenant partager cet album'
        );

        return $this->redirectToRoute('album_all_albums');
    }

    /**
     * @Route("/erase-token/{id}", name="album_erase_token")
     * @IsGranted("ROLE_USER")
     */
    public function eraseTokenSharedAlbumAction($id, AlbumRepository $albumRepository, ObjectManager $manager)
    {
        $currentUser = $this->getUser();
        $sharedAlbum = $albumRepository->find($id);
        $albumAuthor = $sharedAlbum->getAuthor();
        if ($currentUser != $albumAuthor) {
            throw $this->createAccessDeniedException();
        }
        $sharedAlbum->setAlbumToken(null);
        $manager->persist($sharedAlbum);
        $manager->flush();
        $this->addFlash(
            'danger',
            'Cet album n\'est plus partagé'
        );

        return $this->redirectToRoute('album_all_albums');
    }

    /**
     * @route("/shared-album/{id}/{token}", name="album_shared")
     */
    public function sharedAlbumAction($id, $token = null, AlbumRepository $albumRepository)
    {
        $sharedAlbum = $albumRepository->find($id);
        $sharedAlbumAuthorName = $sharedAlbum->getAuthor()->getUsername();
        if ($sharedAlbum->getAlbumToken() == $token) {
            return $this->render('album/album-shared.html.twig', [
                'album' => $sharedAlbum,
                'author' => $sharedAlbumAuthorName,
                'token' => $token
            ]);
        } else {
            $this->addFlash(
                'danger',
                'Cet album n\'est plus partagé'
            );
            return $this->render('album/album-shared.html.twig', [
                'author' => $sharedAlbumAuthorName,
                'token' => $token
            ]);
        }
    }
}
