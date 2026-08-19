<?php

namespace Disjfa\UserBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;

#[AsEventListener]
class AuthenticationSuccessListener
{
    public function __construct(public readonly EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(AuthenticationSuccessEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        $user->setLastLoggedIn(new \DateTimeImmutable());

        $this->entityManager->flush();
    }
}
