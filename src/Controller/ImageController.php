<?php

namespace App\Controller;

use App\Entity\Photo;
use App\Form\PhotoType;
use App\Repository\AlbumRepository;
use App\Repository\PhotoRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ImageController extends AbstractController
{
    /**
     * @Route("/add", name="image_add")
     */
    public function addPhotoAction(Request $request, ObjectManager $manager)
    {
        $photo = new Photo();

        $form = $this->createForm(PhotoType::class, $photo);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $photo->setAuthor($this->getUser());
            $file = $form->get('file')->getData();
            $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
            try {
                $file->move(
                    $this->getParameter('images_directory'),
                    $fileName
                );
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }
            $photo->setFile($fileName);
            $manager->persist($photo);
            $manager->flush();

            return $this->redirectToRoute('image_all_photos');
        }
        return $this->render('image/add-photo.html.twig', [
            'formPhoto' => $form->createView()
        ]);
    }

    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }

    /**
     * @Route("/photos", name="image_all_photos")
     */
    public function showAllPhotosAction(PhotoRepository $photoRepository)
    {
        $currentUser = $this->getUser();
        $photos = $photoRepository->findBy([
            'author' => $currentUser
        ]);
        return $this->render('image/all-photos.html.twig',[
            'photos' => $photos
        ]);
    }

    /**
     * @Route("/edit/{id}", name="image_one_photo")
     */
    public function showOnePhotoAction($id, PhotoRepository $photoRepository, AlbumRepository $albumRepository)
    {
        $photoToEdit = $photoRepository->find($id);
        $currentUser = $this->getUser();
        $userAlbums = $albumRepository->findBy([
            'author' => $currentUser

        ]);
        dump($photoToEdit);
        return $this->render('image/one-photo.html.twig', [
            'photo' => $photoToEdit,
            'albums' => $userAlbums
        ]);
    }

    /**
     * @Route("/remove/{photo}", name="image_remove_photo")
     */
    public function removePhotoAction($photo, ObjectManager $manager, PhotoRepository $photoRepository)
    {
        $photoToRemove = $photoRepository->findBy([
            'file' => $photo
        ]);
        dump($photoToRemove[0]);
        $manager->remove($photoToRemove[0]);
        $manager->flush();
        return $this->redirectToRoute('image_all_photos');
    }

    /**
     * @Route("/add_album/{photoId}", name="image_add_to_album")
     */
    public function addPhotoToAlbumAction($photoId, Request $request, PhotoRepository $photoRepository, AlbumRepository $albumRepository, ObjectManager $manager)
    {
        $albumCompletedId = $request->request->get("select-album");
        $albumCompleted = $albumRepository->find($albumCompletedId);
        $photoToAdd = $photoRepository->find($photoId);
        $photoToAdd->setAlbum($albumCompleted);
        $manager->persist($photoToAdd);
        $manager->flush();

        return $this->redirectToRoute('album_all_albums');
    }
}
