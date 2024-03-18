<?php
// src/Security/Voter/UserVoter.php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class UserVoter extends Voter {
    private Security $security;

    public function __construct(Security $security) {
        $this->security = $security;
    }

    protected function supports($attribute, $subject): bool {
        return in_array($attribute, ['EDIT', 'VIEW']) && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool {
        $currentUser = $token->getUser();

        if (!$currentUser instanceof User) {
            return false;
        }

        /** @var User $user */
        $user = $subject;

        switch ($attribute) {
            case 'EDIT':
                // Admin can always edit
                if ($this->security->isGranted('ROLE_ADMIN')) {
                    return true;
                }

                // Otherwise, users can only edit their own profiles
                return $user->getId() === $currentUser->getId();

            case 'VIEW':
                // Admin can always view
                if ($this->security->isGranted('ROLE_ADMIN')) {
                    return true;
                }

                // Otherwise, users can only view their own profiles
                return $user->getId() === $currentUser->getId();
        }

        return false;
    }
}
