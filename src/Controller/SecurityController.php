<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\PasswordModify;
use App\Entity\User;
use App\Form\LoginType;
use App\Form\PasswordModifyType;
use App\Form\RegistrationType;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Swift_Mailer;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
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

    /**
     * @Route("/mot-de-passe-oublie", name="security_forgot_password")
     */
    public function forgotPassword()
    {
        return $this->render('security/forgotPassword.html.twig');
    }

    private function isRequestInTime(\Datetime $passwordTokenCreatedAt = null)
    {
        if ($passwordTokenCreatedAt === null)
        {
            return false;
        }

        $now = new \DateTime();
        $interval = $now->getTimestamp() - $passwordTokenCreatedAt->getTimestamp();

        $daySeconds = 60 * 10;
        $response = $interval > $daySeconds ? false : $reponse = true;
        return $response;
    }

    /**
     * @Route("/reinitialiser-mot-de-passe/{id}/{token}", name="security_replace_password")
     */
    public function replacePassword($id = null, $token = null, UserRepository $userRepository, Request $request, UserPasswordEncoderInterface $encoder, ObjectManager $manager)
    {
        $user = $userRepository->find($id);

        if ($user->getPasswordToken() === null || $token !== $user->getPasswordToken() || !$this->isRequestInTime($user->getPasswordTokenCreatedAt()))
        {
            throw new AccessDeniedHttpException();
        }

        $passwordModify = new PasswordModify();
        $form = $this->createForm(PasswordModifyType::class, $passwordModify);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $passwordModify->getNewPassword();
            $hash = $encoder->encodePassword($user, $newPassword);
            $user->setPassword($hash);
            $user->setPasswordToken(null);
            $user->setPasswordTokenCreatedAt(null);

            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre mot de passe a bien été modifié, connectez-vous avec votre nouveau mot de passe'
            );
            return $this->redirectToRoute('security_login');
        }
        return $this->render('security/replacePassword.html.twig', [
            'formPasswordModify' => $form->createView()
        ]);
    }
}
