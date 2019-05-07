<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\User;
use App\Form\LoginType;
use App\Form\RegistrationType;
use Doctrine\Common\Persistence\ObjectManager;
use Swift_Mailer;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/inscription", name="security_registration")
     */
    public function registration(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder, Swift_Mailer $mailer)
    {
        $user = new User();

        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash);
            $manager->persist($user);
            $manager->flush();

            $userEmail = $user->getEmail();
            $userName = $user->getUsername();

            $message = (new \Swift_Message('Bienvenue sur Triphotos !'))
                ->setFrom('triphoto.contact@gmail.com')
                ->setTo($userEmail)
                ->setBody(
                    $this->renderView('emails/registration.html.twig',
                        ['name' => $userName]
                    ),
                    'text/html'
                );

            $mailer->send($message);

            return $this->redirectToRoute('security_login');
        }

        return $this->render('security/registration.html.twig', [
            'formRegistration' => $form->createView()
        ]);
    }

    /**
     * @Route("/connexion", name="security_login")
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    /**
     * @Route("/deconnexion", name="security_logout")
     */
    public function logout()
    {
        return $this->redirectToRoute('home');
    }
}
