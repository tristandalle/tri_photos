<?php

namespace App\Controller;
use App\Repository\AlbumRepository;
use App\Repository\PhotoRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swift_Attachment;
use Swift_Image;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class MailerController extends AbstractController
{
    /**
     * @Route("/mailer/{from}/{to}/{currentPath}", name="mailer_contact")
     * @IsGranted("ROLE_USER")
     */
    public function contactAction($from = null, $to = null, $currentPath = null, Request $request, UserRepository $userRepository, Swift_Mailer $mailer)
    {
        $subject = $request->request->get('subject');
        $content = $request->request->get('content');
        if ($currentPath == 'admin') {
            $fromEmail = $from;
            $toEmail = $userRepository->find($to)->getEmail();
            $returnPath = 'admin_members';
            $this->addFlash(
                'success',
                'Message bien envoyé à '.$userRepository->find($to)->getUsername()
            );
        } elseif ($currentPath == 'footer') {
            $fromEmail = $userRepository->find($from)->getEmail();
            $toEmail = $to;
            $returnPath = 'home';
            $this->addFlash(
                'success',
                'Merci de nous avoir contacté, nous vous répondrons dans les plus brefs délais'
            );
        }

        $message = (new \Swift_Message($subject))
            ->setFrom($fromEmail)
            ->setTo($toEmail)
            ->setBody($content,
                'text/html'
            );

        $mailer->send($message);
        return $this->redirectToRoute($returnPath);
    }

    /**
     * @Route("/mailer-photo/{from}/{photoId}/{currentPath}", name="mailer_photo")
     * @IsGranted("ROLE_USER")
     */
    public function sendPhotoAction($from = null, $photoId = null, $currentPath = null, Request $request, UserRepository $userRepository, PhotoRepository $photoRepository, Swift_Mailer $mailer)
    {
        $fromEmail = $userRepository->find($from)->getEmail();
        $fromName = $userRepository->find($from)->getUsername();
        $toEmail = $request->request->get('to-email');
        $content = $request->request->get('content');
        $fileToSend = $photoRepository->find($photoId)->getFile();
        $fileName = $photoRepository->find($photoId)->getOriginalName();
        /*$attachment =  Swift_Attachment::fromPath(getcwd().'/../assets/uploads/images/thumbnails/mini_'.$fileToSend)
            ->setFilename($fileName)
            ->setDisposition('inline');*/

        $message = (new Swift_Message());
            $cid = $message->embed(Swift_Image::fromPath(getcwd().'/../assets/uploads/images/thumbnails/mini_'.$fileToSend)->setFilename($fileName));
            $message->setSubject($fromName.' vous envoie une photo via Triphotos');
            $message->setFrom($fromEmail);
            $message->setTo($toEmail);
            $message->setBody(
                '<html>' .
                ' <body>' .
                '  <p>Voici la photo que <strong>' .$fromName. '</strong> vous envoie : </p><img src="' . $cid . '"/>' .
                ' <p>' . $content . '</p> <p>Vous aussi profitez de vos photos sur <a href="">Triphotos</a></p>' .
                ' </body>' .
                '</html>',
                'text/html'
            );
//            $message->attach($attachment);

        $mailer->send($message);
        $this->addFlash(
            'success',
            'Votre photo a bien été envoyé à l\'adresse : '.$toEmail
        );
        if ($currentPath == 'all-photos') {
            $returnPath = 'image_all_photos';
            return $this->redirectToRoute($returnPath);
        } elseif ($currentPath == 'one-photos') {
            $returnPath = 'image_one_photo';
            return $this->redirectToRoute($returnPath, [
                "id" => $photoId
            ]);
        } elseif ($currentPath == 'one-album') {
            $albumId = $photoRepository->find($photoId)->getAlbum()->getId();
            $returnPath = 'album_one_album';
            return $this->redirectToRoute($returnPath, [
                "id" => $albumId
            ]);
        } elseif ($currentPath == 'album_one_album_all_photos') {
            $albumId = $photoRepository->find($photoId)->getAlbum()->getId();
            $returnPath = 'album_one_album_all_photos';
            return $this->redirectToRoute($returnPath, [
                "id" => $albumId,
                "photoId" => $photoId
            ]);
        } else {
            return $this->redirectToRoute('home');
        }
    }

    /**
     * @Route("/mailer-link/{from}/{albumId}/{currentPath}", name="mailer_link_album")
     * @IsGranted("ROLE_USER")
     */
    public function sendLinkAlbumAction($from = null, $albumId = null, $currentPath = null, Request $request, UserRepository $userRepository, AlbumRepository $albumRepository, Swift_Mailer $mailer)
    {
        $fromEmail = $userRepository->find($from)->getEmail();
        $fromName = $userRepository->find($from)->getUsername();
        $toEmail = $request->request->get('to-email');
        $content = $request->request->get('content');
        $linkToSend = 'http://triphotos.tristandalle.fr/shared-album/'.$albumId.'/'.$albumRepository->find($albumId)->getAlbumToken();
        $albumTitle = $albumRepository->find($albumId)->getTitle();

        $message = (new Swift_Message());
        $message->setSubject($fromName.' vous partage un album via Triphotos');
        $message->setFrom($fromEmail);
        $message->setTo($toEmail);
        $message->setBody(
            '<html>' .
            ' <body>' .
            '  <p><strong>' .$fromName. '</strong> vous partage cet album : <a href="'.$linkToSend.'">'.$albumTitle.'</a></p>' .
            ' <i>' . $content . '</i> <p>Vous aussi profitez de vos photos sur <a href="">Triphotos</a></p>' .
            ' </body>' .
            '</html>',
            'text/html'
        );
        $mailer->send($message);
        $this->addFlash(
            'success',
            'Le lien vers votre album a bien été envoyé à l\'adresse : '.$toEmail
        );
        return $this->redirectToRoute($currentPath);
    }

    /**
     * @Route("/mailer-password", name="mailer_password")
     */
    function sendMailPasswordAction(Request $request, UserRepository $userRepository, TokenGeneratorInterface $tokenGenerator, Swift_Mailer $mailer, ObjectManager $manager)
    {
        $requestEmail = $request->request->get('email');
        $user = $userRepository->findBy(['email' => $requestEmail]);
        if ($user == null) {
            $this->addFlash(
                'danger',
                'Cette adresse email est inconnue'
            );
            return $this->redirectToRoute('security_forgot_password');
        }else {
            $user = $user[0];
            $user->setPasswordToken($tokenGenerator->generateToken());
            $user->setPasswordTokenCreatedAt(new \Datetime());
            $manager->persist($user);
            $manager->flush();

            $userEmail = $user->getEmail();
            $userName = $user->getUsername();

            $message = (new \Swift_Message('Réinitialiser votre mot de passe Triphotos !'))
                ->setFrom(getenv('ADMIN_MAIL'))
                ->setTo($userEmail)
                ->setBody(
                    $this->renderView('emails/linkToModifyPassword.html.twig', [
                        'name' => $userName,
                        'user' => $user
                    ]),
                    'text/html'
                );

            $mailer->send($message);
            $this->addFlash(
                'success',
                'Un email vient de vous être envoyé pour réinitialiser votre mot de passe'
            );

            return $this->redirectToRoute('security_forgot_password');

        }
    }
}
