<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Photo;
use App\Form\AlbumType;
use App\Repository\AlbumRepository;
use App\Repository\PhotoRepository;
use App\Service\Paginator;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class AlbumController extends AbstractController
{
    /**
     * @Route("/add/album", name="album_add")
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
     * @Route("/album/{id}/{filter}/{page<\d+>?1}", defaults={"filter"=0}, name="album_one_album")
     */
    public function showOneAlbumAction($filter, $id, $page, Paginator $paginator, AlbumRepository $albumRepository)
    {
        $albumToEdit = $albumRepository->find($id);
        $paginator->setEntityClass(Photo::class)
            ->setCurrentPage($page)
            ->setLimit(20)
            ->setWhere(['album' => $albumToEdit]);

        if ($filter != 0) {
            $photos = $albumRepository->find($albumToEdit)->getPhotos();
        } else {
            $photos = $paginator->getData();
        }

        return $this->render('album/one-album.html.twig', [
            'total' => count($albumToEdit->getPhotos()),
            'photos' => $photos,
            'pages' => $paginator->getPages(),
            'page' => $page,
            'album' => $albumToEdit,
            'filter' => $filter
        ]);
    }

    /**
     * @Route("/album/{id}/all/{photoId}", name="album_one_album_all_photos")
     */
    public function showAllPhotosOfOneAlbumAction($id, $photoId, AlbumRepository $albumRepository)
    {
        $albumToEdit = $albumRepository->find($id);
        $authorAlbums = $albumToEdit->getAuthor()->getAlbums();
        if (count($albumToEdit->getPhotos()) == 0) {

        } else {

        }
        return $this->render('album/one-album-all-photos.html.twig', [
            'album' => $albumToEdit,
            'photoId' => $photoId,
            'albums' => $authorAlbums
        ]);
    }

    /**
     * @Route("/album/remove/{id}", name="album_remove_album")
     */
    public function removeAlbumAction($id, AlbumRepository $albumRepository, PhotoRepository $photoRepository, ObjectManager $manager)
    {
        $albumToRemove = $albumRepository->find($id);
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
     */
    public function generateTokenSharedAlbumAction($id, AlbumRepository $albumRepository, ObjectManager $manager, TokenGeneratorInterface $tokenGenerator)
    {
        $token = $tokenGenerator->generateToken();
        $sharedAlbum = $albumRepository->find($id);
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
     */
    public function eraseTokenSharedAlbumAction($id, AlbumRepository $albumRepository, ObjectManager $manager)
    {
        $sharedAlbum = $albumRepository->find($id);
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
