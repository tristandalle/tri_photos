<?php

namespace App\Controller;
use App\Repository\UserRepository;
use Swift_Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MailerController extends AbstractController
{
    /**
     * @Route("/mailer/{from}/{to}/{currentPath}", name="mailer_contact")
     */
    public function contactAction($from = null, $to = null, $currentPath = null, Request $request, UserRepository $userRepository, Swift_Mailer $mailer)
    {
        $subject = $request->request->get('subject');
        $content = $request->request->get('content');
        if ($currentPath == 'admin') {
            $fromEmail = $from;
            $toEmail = $userRepository->find($to)->getEmail();
            $returnPath = 'admin_members';
        } elseif ($currentPath == 'footer') {
            $fromEmail = $userRepository->find($from)->getEmail();
            $toEmail = $to;
            $returnPath = 'home_connected';
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
}
