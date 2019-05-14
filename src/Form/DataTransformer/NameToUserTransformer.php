<?php
namespace App\Form\DataTransformer;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class NameToUserTransformer implements DataTransformerInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function transform($user)
    {
        if (null === $user) {
            return '';
        }

        return $user->getUsername();
    }

    public function reverseTransform($user) {
        if (!$user) {
            return;
        }

        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => $user])
        ;

        if (null === $user) {
            throw new TransformationFailedException(sprintf(
                'A user by the name "%s" does not exist!',
                $user
            ));
        }

        return $user;
    }
}