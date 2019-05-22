<?php
namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    const EDIT = 'edit';

    protected function supports($attribute, $entity)
    {
        if (!in_array($attribute, array(self::EDIT))) {
            return false;
        }

        if (!$entity instanceof User) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $entity, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        $self = $entity;

        switch ($attribute) {
            case self::EDIT: return $this->canEdit($self, $user);
        }

        throw new \LogicException('How did you even get here.');
    }

    private function canEdit(User $self, User $user)
    {
        // Users can edit themselves.
        return $user === $self;
    }
}