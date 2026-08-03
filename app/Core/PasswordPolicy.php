<?php

namespace App\Core;

/**
 * Règle de robustesse des mots de passe, partagée par l'inscription et la
 * réinitialisation. La constante REQUIREMENTS est aussi affichée au-dessus
 * du champ « mot de passe » pour guider l'utilisateur.
 */
class PasswordPolicy
{
    public const REQUIREMENTS = 'Au moins 8 caractères, dont une majuscule, une minuscule, un chiffre et un caractère spécial.';

    public static function isValid(string $password): bool
    {
        return mb_strlen($password) >= 8
            && preg_match('/[A-Z]/', $password) === 1
            && preg_match('/[a-z]/', $password) === 1
            && preg_match('/[0-9]/', $password) === 1
            && preg_match('/[^A-Za-z0-9]/', $password) === 1;
    }
}
