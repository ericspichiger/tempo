<?php

namespace App\Security\Voter;

use App\Entity\Task;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['edit', 'delete'])
            && $subject instanceof Task;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case 'edit':
                return $user->getCompany() === $subject->getCompany();
            case 'delete':
                return $user->getCompany() === $subject->getCompany();
        }

        return false;
    }
}
