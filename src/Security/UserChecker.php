<?php
// src/Security/UserChecker.php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Core\Exception\PreAuthenticationException;

class UserChecker implements UserCheckerInterface
{
    /**
     * Méthode appelée avant l'authentification.
     * Vous pouvez vérifier ici si l'utilisateur peut se connecter (avant d'être authentifié).
     */
    public function checkPreAuth(UserInterface $user)
    {
        // Exemple : Vérifier si l'utilisateur n'est pas désactivé avant l'authentification
        if (!$user->isActive()) {
            throw new PreAuthenticationException('Your account is deactivated.');
        }
    }

    /**
     * Méthode appelée après l'authentification.
     * Vous pouvez effectuer des vérifications après que l'utilisateur ait été authentifié.
     */
    public function checkPostAuth(UserInterface $user)
    {
        // Exemple : Si l'utilisateur est désactivé après authentification
        if (!$user->isEnabled()) {
            throw new LockedException('Your account is locked.');
        }
    }
}
