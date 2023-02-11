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
                // logic to determine if the user can EDIT
                // return true or false
                break;
            case self::VIEW:
                return $this->view($subject, $user);
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
}
