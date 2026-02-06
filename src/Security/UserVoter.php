<?php

namespace Disjfa\UserBundle\Security;

use Disjfa\UserBundle\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    public const VIEW = 'view';
    public const UPDATE = 'update';
    public const VIEW_ALL = 'view_all';
    public const CREATE = 'create';

    public function __construct(private readonly Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (User::class === $subject && in_array($attribute, [self::VIEW_ALL, self::CREATE])) {
            return true;
        }

        // VIEW and UPDATE require a User subject
        return in_array($attribute, [self::VIEW, self::UPDATE])
            && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return match ($attribute) {
            self::VIEW_ALL, self::CREATE, self::VIEW, self::UPDATE => $this->security->isGranted('ROLE_ADMIN'),
            default => false,
        };
    }
}
