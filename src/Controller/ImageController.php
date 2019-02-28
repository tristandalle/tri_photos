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
    public function addPhotoAction(Request $request, ObjectManager $manager, AlbumRepository $albumRepository)
    {
        $photo = new Photo();

        $form = $this->createForm(PhotoType::class, $photo);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())

        {
            if ($request->request->get("select-album") != null) {
                $albumCompleted = $albumRepository->find($request->request->get("select-album"));
                $photo->setAlbum($albumCompleted);
            }
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
     * @Route("/download/{id}", name="file_download")
     */
    function downloadForDisplayPhoto($id, PhotoRepository $photoRepository)
    {
        $fileName = $photoRepository->find($id)->getFile();
        $filePath = $this->getParameter('images_directory'). '/'.$fileName;
        return $this->file($filePath);
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
        $author = $photoToEdit->getAuthor();
        if ($currentUser != $author)
        {
            http_response_code(401);
            die();
        }
        return $this->render('image/one-photo.html.twig', [
            'photo' => $photoToEdit,
            'albums' => $userAlbums
        ]);
    }

    /**
     * @Route("/remove/{id}", name="image_remove_photo")
     */
    public function removePhotoAction($id, ObjectManager $manager, PhotoRepository $photoRepository)
    {
        $photoToRemove = $photoRepository->find($id);
        $manager->remove($photoToRemove);
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

        return $this->redirectToRoute('image_one_photo', array(
            'id' => $photoId
        ));
    }

    /**
     * @Route("/remove_album/{id}/{albumId}", name="image_remove_to_album")
     */
    public function removePhotoToAlbumAction($id,PhotoRepository $photoRepository, ObjectManager $manager, $albumId)
    {
        $photoToRemoveFromAlbum = $photoRepository->find($id);
        $photoToRemoveFromAlbum->setAlbum(null);
        $manager->persist($photoToRemoveFromAlbum);
        $manager->flush();

        return $this->redirectToRoute('album_one_album', array(
            'id' => $albumId
        ));
    }

    /**
     * @Route("/rating/{id}/{rating}", name="image_rating")
     */
    public function ratingPhotoAction($id, $rating, PhotoRepository $photoRepository, ObjectManager $manager)
    {
        $photoToRating = $photoRepository->find($id);
        $photoActualRating = $photoToRating->getRating();
        if ($rating == $photoActualRating) {
            $rating--;
        }
        $photoToRating->setRating($rating);
        $manager->persist($photoToRating);
        $manager->flush();

        return $this->redirectToRoute('image_one_photo', [
            'id' => $id
        ]);
    }
}
