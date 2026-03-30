<?php

namespace App\Security;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;

class OAuthUserProvider implements OAuthAwareUserProviderInterface
{
    private EntityManagerInterface $em;
    private string $userClass;
    private ?SessionInterface $session;

    public function __construct(EntityManagerInterface $em, string $userClass, RequestStack $requestStack)
    {
        $this->em = $em;
        $this->userClass = $userClass;
        $this->session = $requestStack->getSession();
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response): UserInterface
    {
        $email = $response->getEmail();
        $user = $this->em->getRepository($this->userClass)->findOneBy(['email' => $email]);

        if (!$user) {
            $user = new $this->userClass();

            $user->setNom($response->getNickname());
            $user->setPrenom($response->getNickname());
            $user->setEmail($email);
            $user->setGoogleId($response->getUserIdentifier());
            $user->setPassword($response->getUsername());
            $user->setConfirmPassword($response->getUsername());
            $user->setRoles(['ROLE_USER']);
            $user->setNumTelephone('00000000');
            $user->setDateNaissance(new \DateTime('now'));
            $user->setLocalisation('Paris');
            $user->setBanned(0);
            $user->setDeleted(0);
            $responseData = $response->getData();
            $profilePicture = $responseData['picture'] ?? null;
            $user->setImage($profilePicture ?: 'inconnu.jpg');
            $this->em->persist($user);
            $this->em->flush();
            $this->session->set('google_id', $user->getGoogleId());
            $this->session->set('email', $user->getEmail());
            $this->session->set('id', $user->getId());
             $this->session->set('test', $user->getEmail());
             $this->session->set('user_nom', $user->getNom());
             $this->session->set('user_prenom', $user->getPrenom());
             $this->session->set('localisation', $user->getLocalisation());
             $this->session->set('image', $user->getImage());
             $this->session->set('roles', $user->getRoles());
             $this->session->set('num', $user->getNumTelephone());
        }else{
            $this->session->set('google_id', $user->getGoogleId());
             $this->session->set('email', $user->getEmail());
             $this->session->set('id', $user->getId());
             $this->session->set('test', $user->getEmail());
             $this->session->set('user_nom', $user->getNom());
             $this->session->set('user_prenom', $user->getPrenom());
             $this->session->set('localisation', $user->getLocalisation());
             $this->session->set('image', $user->getImage());
             $this->session->set('roles', $user->getRoles());
        }

        return $user;
    }
}