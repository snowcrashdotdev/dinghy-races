<?php
namespace App\Form\DataTransformer;

use App\Entity\Game;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class NameToGameTransformer implements DataTransformerInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function transform($game)
    {
        if (null === $game) {
            return '';
        }

        return $game->getDescription();
    }

    public function reverseTransform($game) {
        if (!$game) {
            return;
        }

        $game = $this->entityManager
            ->getRepository(Game::class)
            ->findOneBy(['description' => $game])
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