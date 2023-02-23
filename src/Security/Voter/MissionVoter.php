<?php

namespace App\Security\Voter;

use App\Entity\Mission;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class MissionVoter extends Voter
{
    public const EDIT = 'POST_EDIT';
    public const VIEW = 'POST_VIEW';
    public const DELETE = 'POST_DELETE';

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW])
            && $subject instanceof Mission;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::EDIT:
                return $this->edit($subject, $user);
                break;
            case self::VIEW:
                return $this->view($subject, $user);
                break;
            case self::DELETE:
                return $this->edit($subject, $user);
                break;
        }

        return false;
    }

    private function view(Mission $mission, UserInterface $user)
    {
        if(in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        if (in_array('ROLE_CLIENT', $user->getRoles())) {
            return $mission->getClient() === $user;
        }


        return $mission->getStatus() === 'free' || $mission->getAgent() === $user;
    }

    private function edit(Mission $mission, UserInterface $user)
    {
        if(in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        return in_array('ROLE_CLIENT', $user->getRoles()) && $mission->getClient() === $user;

    }
}
