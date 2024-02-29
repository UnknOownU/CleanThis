<?php

namespace App\Security;

use App\Entity\Users;
use App\Repository\UsersRepository;
use App\Security\AbstractOAuthAuthenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;

// class GoogleAuthenticator extends AbstractAuthenticator

// {
//     public function supports(Request $request): ?bool
//     {
//         // TODO: Implement supports() method.
//     }

//     public function authenticate(Request $request): Passport
//     {
//         // TODO: Implement authenticate() method.
//     }

//     public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
//     {
//         // TODO: Implement onAuthenticationSuccess() method.
//     }

//     public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
//     {
//         // TODO: Implement onAuthenticationFailure() method.
//     }
// fin de la classe 

    //    public function start(Request $request, AuthenticationException $authException = null): Response
    //    {
    //        /*
    //         * If you would like this class to control what happens when an anonymous user accesses a
    //         * protected page (e.g. redirect to /login), uncomment this method and make this class
    //         * implement Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface.
    //         *
    //         * For more details, see https://symfony.com/doc/current/security/experimental_authenticators.html#configuring-the-authentication-entry-point
    //         */
    //    }


// video 

class GoogleAuthenticator extends AbstractOAuthAuthenticator
{
    protected string $serviceName = 'google';
    
    // protected function getUserFromResourceOwner(ResourceOwnerInterface $resourceOwner,UserRepository $repository): ?Users

    protected function getUserFromResourceOwner(ResourceOwnerInterface $resourceOwner, UsersRepository $repository): ?Users
  

{
    if(!($resourceOwner instanceof GoogleUser)){
       throw new \RuntimeException("excepting google Users");
    }
    if (true !=($resourceOwner->toArray()['email_verified']?? null)){
        throw new AuthenticationException("email not verified");
    }

    return $repository->findOneBy([
        "google_id" => $resourceOwner->getId(),
        "email"=> $resourceOwner->getEmail(),
        "lastname" => $resourceOwner->getLastName(),
        "firstname" => $resourceOwner->getFirstName()
    ]);
}
}

