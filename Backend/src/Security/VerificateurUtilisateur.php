<?php

namespace App\Security;

use App\Entity\Utilisateur;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Refuse la connexion tant que l'adresse e-mail de l'utilisateur n'est pas confirmée.
 */
class VerificateurUtilisateur implements UserCheckerInterface
{
    public const MESSAGE_NON_CONFIRME = 'Confirme ton adresse e-mail avant de te connecter.';

    public function checkPreAuth(UserInterface $user): void
    {
        if ($user instanceof Utilisateur && !$user->isEmailConfirme()) {
            throw new CustomUserMessageAccountStatusException(self::MESSAGE_NON_CONFIRME);
        }
    }

    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
    }
}
