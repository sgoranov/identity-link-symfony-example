<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * The signed-in user, identified by Identity Link's "sub" claim and kept in the session.
 */
final class User implements UserInterface
{
    public function __construct(private readonly string $identifier)
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->identifier;
    }

    public function getRoles(): array
    {
        // Identity Link's UserInfo response has no groups yet, so every user gets the same role.
        return ['ROLE_USER'];
    }
}
