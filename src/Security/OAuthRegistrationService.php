<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

final class OAuthRegistrationService

{
    public function __construct(
        private UserRepository $repository
    ) {
    }

    /**
     * @param GoogleUser $resourceOwner
     * 
     */
    public function persist(ResourceOwnerInterface $resourceOwner): User {
        $user = (new User())
                ->setEmail($resourceOwner->getEmail())
                ->setIdGoogle($resourceOwner->getId())
                ->setName($resourceOwner->getLastName())
                ->setFirstname($resourceOwner->getFirstName());

        $this->repository->add($user, flush: true);
        return $user;
    }
}
