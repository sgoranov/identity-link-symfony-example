<?php

declare(strict_types=1);

namespace App\Security;

use Drenso\OidcBundle\Model\OidcTokens;
use Drenso\OidcBundle\Model\OidcUserData;
use Drenso\OidcBundle\Security\UserProvider\OidcUserProviderInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * The app has no user store: the user lives in the session, and the bundle's token keeps the UserInfo claims.
 *
 * The user is not checked against Identity Link again, so a user deleted there stays signed in here until the
 * session ends. An app with its own users creates them in ensureUserExists() and loads them in loadOidcUser().
 *
 * @implements OidcUserProviderInterface<User>
 */
final class UserProvider implements OidcUserProviderInterface
{
    public function ensureUserExists(string $userIdentifier, OidcUserData $userData, OidcTokens $tokens): void
    {
    }

    public function loadOidcUser(string $userIdentifier): UserInterface
    {
        return new User($userIdentifier);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return $class === User::class;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        // Users exist only in the session, after signing in with Identity Link.
        throw new UserNotFoundException();
    }
}
