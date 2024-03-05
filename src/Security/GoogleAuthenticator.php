<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\AbstractOAuthAuthenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;


class GoogleAuthenticator extends AbstractOAuthAuthenticator
{
    protected string $serviceName = 'google';
    
    // protected function getUserFromResourceOwner(ResourceOwnerInterface $resourceOwner,UserRepository $repository): ?user

    protected function getUserFromResourceOwner(ResourceOwnerInterface $resourceOwner, UserRepository $repository): ?User
  

{
    if(!($resourceOwner instanceof GoogleUser)){
       throw new \RuntimeException("excepting google user");
    }
    if (true !=($resourceOwner->toArray()['email_verified']?? null)){
        throw new AuthenticationException("email not verified");
    }

    return $repository->findOneBy([
        "id_google" => $resourceOwner->getId(),
        "email"=> $resourceOwner->getEmail(),
        "name" => $resourceOwner->getName(),
        "firstname" => $resourceOwner->getFirstName()
    ]);
}
}