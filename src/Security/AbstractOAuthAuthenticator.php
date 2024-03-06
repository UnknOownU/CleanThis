<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use KnpU\OAuth2ClientBundle\Client\OAuth2Client;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

abstract class  AbstractOAuthAuthenticator extends OAuth2Authenticator
{
    use TargetPathTrait;
    protected string $serviceName = '';

    public function __construct(
        private readonly ClientRegistry $clientRegistry, 
        private readonly RouterInterface $router,
        private readonly UserRepository $repository,
        private readonly OAuthRegistrationService $registrationService,
        private EntityManagerInterface $entityManager 
        ) 
    {
    }

    public function supports(Request $request): ?bool
    {
        return 'auth_oauth_check' == $request->attributes->get('_route') && $request->get('service') == $this->serviceName;
    }


    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $targetPath = $this->getTargetPath($request->getSession(), $firewallName);
        if ($targetPath){
            return new RedirectResponse($targetPath);
        }
        return new RedirectResponse($this->router->generate('admin'));
    }
 

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        if ($request->hasSession()) {
            $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);
        }
        return new RedirectResponse($this->router->generate('app_login'));
    }
   
    public function authenticate(Request $request): Passport
    {
        $credentials = $this->fetchAccessToken($this->getClient());
        $googleUser = $this->getClient()->fetchUserFromToken($credentials);
    
        // Vérifiez si l'e-mail existe dans votre base de données
        $email = $googleUser->getEmail();
        $existingUser = $this->repository->findOneByEmail($email);
    
        if ($existingUser) {
            // Associez l'ID Google à l'utilisateur existant si nécessaire
            if ($existingUser->getIdGoogle() !== $googleUser->getId()) {
                $existingUser->setIdGoogle($googleUser->getId());
                // Enregistrez l'utilisateur mis à jour dans la base de données
                $this->entityManager->persist($existingUser);
                $this->entityManager->flush();
            }
    
            // Connectez l'utilisateur
            return new SelfValidatingPassport(new UserBadge($email));
        } else {
            // Gérer le cas où l'utilisateur n'existe pas
            throw new CustomUserMessageAuthenticationException('Aucun utilisateur associé à cet e-mail Google.');
        }
    } 
protected function getResourceownerFromCredentials(AccessToken $credentials): ResourceOwnerInterface{
    return $this->getClient()->fetchUserFromToken($credentials);
}

private function getClient(): OAuth2ClientInterface
{
    return $this->clientRegistry->getClient($this->serviceName);
}

abstract protected function getUserFromResourceOwner(ResourceOwnerInterface $resourceOwner, UserRepository $repository): ?User;


}