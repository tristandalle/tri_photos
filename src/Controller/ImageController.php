<?php

namespace App\Controller;

use App\Entity\Photo;
use App\Form\PhotoType;
use App\Repository\AlbumRepository;
use App\Repository\PhotoRepository;
use App\Service\Paginator;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Intervention\Image\ImageManagerStatic as Image;

class ImageController extends AbstractController
{
    /**
     * @Route("/add", name="image_add")
     */
    public function addPhotoAction(Request $request, ObjectManager $manager, AlbumRepository $albumRepository)
    {
        $form = $this->createForm(PhotoType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $files = $form->get('file')->getData();

            foreach ($files as $file) {
                if ($file->getMimeType() != "image/jpeg" && $file->getMimeType() != "image/jpg" && $file->getMimeType() != "image/png") {
                    $errorFileFormat = 'Le format du fichier : ' . $file->getClientOriginalName() . ' n\'est pas accepté.';
                }
                if (isset($errorFileFormat)) {
                    $errorsFileFormat[] = $errorFileFormat;
                }
            }
            if (isset($errorsFileFormat)) {
                return $this->render('photos/add-photo.html.twig', [
                    'formPhoto' => $form->createView(),
                    'errorsFileFormat' => $errorsFileFormat
                ]);
            }

            foreach ($files as $file) {
                $photo = new Photo();
                if ($request->request->get("select-album") != null) {
                    $albumCompleted = $albumRepository->find($request->request->get("select-album"));
                    $photo->setAlbum($albumCompleted);
                }
                $photo->setAuthor($this->getUser());
                $photo->setOriginalName($file->getClientOriginalName());
                $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('images_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                Image::configure(array('driver' => 'gd'));
                $img = Image::make($this->getParameter('images_directory') . "/" . $fileName)->orientate();
                $img->resize(null, 600, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $img->save($this->getParameter('thumbnails_directory') . "/mini_" . $fileName);
                $photo->setFile($fileName);
                $manager->persist($photo);
            }
            $manager->flush();

            return $this->redirectToRoute('image_all_photos');
        }
        return $this->render('photos/add-photo.html.twig', [
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
        $filePath = $this->getParameter('thumbnails_directory') . '/mini_' . $fileName;
        return $this->file($filePath);
    }

    /**
     * @Route("/photos/{filter}/{page<\d+>?1}", defaults={"filter"=0}, name="image_all_photos")
     */
    public function showAllPhotosAction($filter, $page, Paginator $paginator, PhotoRepository $photoRepository)
    {
        $currentUser = $this->getUser();
        $paginator->setEntityClass(Photo::class)
            ->setCurrentPage($page)
            ->setLimit(20)
            ->setWhere(['author' => $currentUser]);

        if ($filter != 0) {
            $photos = $photoRepository->findBy(['author' => $currentUser]);
        } else {
            $photos = $paginator->getData();
        }

        return $this->render('photos/all-photos.html.twig', [
            'total' => $photoRepository->findBy(['author' => $this->getUser()]),
            'photos' => $photos,
            'pages' => $paginator->getPages(),
            'page' => $page,
            'filter' => $filter
        ]);
    }

    /**
     * @Route("/edit/{id}", name="image_one_photo")
     */
    public function showOnePhotoAction($id, PhotoRepository $photoRepository, AlbumRepository $albumRepository)
    {
        $photoToEdit = $photoRepository->find($id);
        $currentUser = $this->getUser();
        $allUserPhotos = $photoRepository->findBy(
            array('author' => $currentUser)
        );
        $userAlbums = $albumRepository->findBy([
            'author' => $currentUser
        ]);
        $author = $allUserPhotos[0]->getAuthor();
        if ($currentUser != $author) {
            http_response_code(401);
            die();
        }
        return $this->render('photos/one-photo.html.twig', [
            'photo' => $photoToEdit,
            'allphotos' => $allUserPhotos,
            'albums' => $userAlbums
        ]);
    }

    /**
     * @Route("/remove/{id}/{currentPath}", name="image_remove_photo")
     */
    public function removePhotoAction($id, $currentPath = 'undefined', ObjectManager $manager, PhotoRepository $photoRepository, AlbumRepository $albumRepository)
    {
        $photoToRemove = $photoRepository->find($id);
        $currentAlbum = $photoToRemove->getAlbum();
        $idPhotoToRemove = $photoToRemove->getId();
        $currentUser = $this->getUser();
        $allUserPhotos = $photoRepository->findBy(
            array('author' => $currentUser)
        );
        if ($currentAlbum != null) {
            $idCurrentAlbum = $currentAlbum->getId();
        }
        $manager->remove($photoToRemove);
        $manager->flush();
        $idOfOneUserPhoto = $allUserPhotos[0]->getId();
        if ($currentPath == 'albumAllPhotos') {
            return $this->redirectToRoute('album_one_album_all_photos', [
                'id' => $idCurrentAlbum,
                'photoId' => $idPhotoToRemove
            ]);
        } elseif ($currentPath == 'onePhoto') {
            return $this->redirectToRoute('image_one_photo', [
                'id' => $idOfOneUserPhoto
            ]);
        } else {
            return $this->redirectToRoute('image_all_photos');
        }
    }

    /**
     * @Route("/add_album/{photoId}/{currentPath}/{idCurrentAlbum}", name="image_add_to_album")
     */
    public function addPhotoToAlbumAction($photoId, $currentPath = 'undefined', $idCurrentAlbum = 'undefined', Request $request, PhotoRepository $photoRepository, AlbumRepository $albumRepository, ObjectManager $manager)
    {
        $albumCompletedId = $request->request->get("select-album");
        $albumCompleted = $albumRepository->find($albumCompletedId);
        $photoToAdd = $photoRepository->find($photoId);
        $photoToAdd->setAlbum($albumCompleted);
        $manager->persist($photoToAdd);
        $manager->flush();

        if ($currentPath == 'albumAllPhotos') {
            return $this->redirectToRoute('album_one_album_all_photos', [
                'id' => $idCurrentAlbum,
                'photoId' => $photoId
            ]);
        } else {
            return $this->redirectToRoute('image_one_photo', array(
                'id' => $photoId
            ));
        }
    }

    /**
     * @Route("/remove_album/{id}/{albumId}", name="image_remove_to_album")
     */
    public function removePhotoToAlbumAction($id, PhotoRepository $photoRepository, ObjectManager $manager, $albumId)
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
     * @Route("/rating/{id}/{rating}/{currentPath}", name="image_rating")
     */
    public function ratingPhotoAction($id, $rating, $currentPath, PhotoRepository $photoRepository, ObjectManager $manager)
    {
        $photoToRating = $photoRepository->find($id);
        $photoActualRating = $photoToRating->getRating();
        if ($rating == $photoActualRating) {
            $rating--;
        }
        $photoToRating->setRating($rating);
        $currentAlbum = $photoToRating->getAlbum();
        if ($currentAlbum != null) {
            $idCurrentAlbum = $currentAlbum->getId();
        }
        $currentPhoto = $photoToRating->getId();

        $manager->persist($photoToRating);
        $manager->flush();

        if ($currentPath == 'onePhoto') {
            return $this->redirectToRoute('image_one_photo', [
                'id' => $id
            ]);
        } elseif ($currentPath == 'album') {
            return $this->redirectToRoute('album_one_album', [
                'id' => $idCurrentAlbum
            ]);
        } elseif ($currentPath == 'albumAllPhotos') {
            return $this->redirectToRoute('album_one_album_all_photos', [
                'id' => $idCurrentAlbum,
                'photoId' => $currentPhoto
            ]);
        } else {
            return $this->redirectToRoute('image_all_photos');
        }
    }

    /**
     * @Route("/filter/{rating}/{currentPath}/{albumId}", name="filter_rating")
     */
    public function filterRatingAction($rating, $currentPath = 'undefined', $albumId = 'undefined')
    {
        $filter = $rating;
        if ($currentPath == 'album_one_album') {
            return $this->redirectToRoute('album_one_album', [
                'id' => $albumId,
                'filter' => $filter
            ]);
        } else {
            return $this->redirectToRoute('image_all_photos', [
                'filter' => $filter
            ]);
        }
    }


}
