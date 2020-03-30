<?php
namespace App\Form\DataTransformer;

use App\Entity\Game;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class GameToRomNameTransformer implements DataTransformerInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Transforms entity (Game) into  a string (ROM filename)
     */
    public function transform($game)
    {
        if (null === $game) {
            return '';
        }

        return $game->getName();
    }

    /**
     * Transforms a string (ROM filename) into a Game entity
     */
    public function reverseTransform($game) {
        if (!$game) {
            return;
        }

        $game = $this->entityManager
            ->getRepository(Game::class)
            ->findOneBy(['name' => $game])
        ;

        if (null === $game) {
            throw new TransformationFailedException(sprintf(
                'The game "%s" does not exist!',
                $game
            ));
        }

        return $game;
    }
}