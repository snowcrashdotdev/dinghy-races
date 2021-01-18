<?php
namespace App\Form\DataTransformer;

use App\Entity\User;
use App\Entity\TournamentUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class UserToUsernameTransformer implements DataTransformerInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Transforms entity (Game) into  a string (ROM filename)
     */
    public function transform($user)
    {
        if (null === $user) {
            return '';
        }

        return $user->getUsername();
    }

    /**
     * Transforms a string (ROM filename) into a Game entity
     */
    public function reverseTransform($user) {
        if (!$user) {
            return;
        }

        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => $user])
        ;

        $tournamentUser = $this->entityManager
            ->getRepository(TournamentUser::class)
            ->findOneBy([
                'user' => $user
            ])
        ;

        if (null === $tournamentUser) {
            throw new TransformationFailedException(sprintf(
                'The user "%s" does not exist!',
                $user
            ));
        }

        return $tournamentUser;
    }
}