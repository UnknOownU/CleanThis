<?php
namespace App\Security;

use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Entity\Operation;
use App\Entity\User; // Assurez-vous que ce soit le bon chemin pour votre entité User

class OperationVoter extends Voter {
    protected function supports(string $attribute, $subject): bool {
        return in_array($attribute, ['EDIT', 'VIEW', 'DELETE'])
            && $subject instanceof Operation;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool {
        $user = $token->getUser();
        $operation = $subject;

        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case 'EDIT':
            case 'VIEW':
            case 'DELETE':
                return $operation->getCustomer() === $user;
        }

        return false;
    }
}
