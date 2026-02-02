<?php

declare(strict_types=1);

namespace Disjfa\UserBundle\Controller;

use Disjfa\UserBundle\Entity\User;
use Disjfa\UserBundle\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Exception\RfcComplianceException;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkNotification;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'disjfa_user_security_login')]
    public function requestLoginLink(
        NotifierInterface $notifier,
        LoginLinkHandlerInterface $loginLinkHandler,
        UserRepository $userRepository,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        // check if form is submitted
        if ($request->isMethod('POST')) {
            // load the user in some way (e.g. using the form input)
            $email = $request->getPayload()->get('email');

            try {
                new Address($email);
            } catch (RfcComplianceException) {
                return new RedirectResponse($this->generateUrl('disjfa_user_security_login'));
            }

            if (false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return new RedirectResponse($this->generateUrl('disjfa_user_security_login'));
            }

            $user = $userRepository->findOneBy(['email' => $email]);

            if (null === $user) {
                $user = new User();
                $user->setEmail($email);
                $user->setPassword(base64_encode(uniqid()));

                $entityManager->persist($user);
                $entityManager->flush();
            }
            $loginLinkDetails = $loginLinkHandler->createLoginLink($user);

            $notification = new LoginLinkNotification($loginLinkDetails, 'Welcome to MOZAIC!');
            $recipient = new Recipient($user->getEmail());

            $notifier->send($notification, $recipient);

            return $this->redirectToRoute('disjfa_user_security_login_sent');
        }

        // if it's not submitted, render the form to request the "login link"
        return $this->render('@DisjfaUser/security/request_login_link.html.twig');
    }

    #[Route('/login/sent', name: 'disjfa_user_security_login_sent')]
    public function loginSent(): Response
    {
        return $this->render('@DisjfaUser/security/login_link_sent.html.twig');
    }

    #[Route('/login-check', name: 'disjfa_user_security_login_check')]
    public function loginCheck()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the login firewall.');
    }

    #[Route('/logout', name: 'disjfa_user_security_logout')]
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the login firewall.');
    }
}
