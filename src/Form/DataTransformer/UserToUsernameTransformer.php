<?php
namespace App\Form\DataTransformer;

use App\Entity\User;
use App\Entity\Tournament;
use App\Entity\TournamentUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class UserToUsernameTransformer implements DataTransformerInterface
{
    private $entityManager;
    private $tournament;

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
    public function reverseTransform($username) {
        $tournamentUser = $this->entityManager
            ->getRepository(TournamentUser::class)
            ->createQueryBuilder('p')
            ->join('p.user', 'u')
            ->where('u.username = :username')
            ->andWhere('p.tournament = :tournament')
            ->setParameter('username', $username)
            ->setParameter('tournament', $this->getTournament())
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (null === $tournamentUser) {
            throw new TransformationFailedException(sprintf(
                'The user "%s" does not exist!',
                $user
            ));
        }

        return $tournamentUser;

    }

    public function setTournament(Tournament $tournament) {
        $this->tournament = $tournament;
    }

    public function getTournament() : Tournament {
        return $this->tournament;
    }
}