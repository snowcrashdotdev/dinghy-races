<?php
namespace App\Security;

use App\Entity\User;
use App\Entity\Tournament;
use App\Entity\TournamentScore;
use App\Repository\TournamentUserRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ScoreVoter extends Voter
{
    const SUBMIT = 'submit_score';

    private $tournament_users;

    public function __construct(TournamentUserRepository $users)
    {
        $this->tournament_users = $users;
    }

    protected function supports(string $attribute, $subject)
    {
        if (!in_array($attribute, [self::SUBMIT])) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject=null, TokenInterface $token)
    {
        $app_user = $token->getUser();

        if (!$app_user instanceof User) {
            return false;
        }

        switch($attribute) {
            case self::SUBMIT:
                return $this->canSubmit($app_user, $subject);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canSubmit(User $user, Tournament $tournament)
    {
        $now = new \DateTime('NOW');
        if (($tournament->getStartDate() > $now) || ($tournament->getEndDate() < $now)) {
            return false;
        }

        $tournament_user = $this->tournament_users->findOneBy([
            'user' => $user,
            'tournament' => $tournament
        ]);

        if (empty($tournament_user)) {
            return false;
        }

        if ($tournament->getFormat() === 'TEAM' && empty($tournament_user->getTeam())) {
            return false;
        }

        return true;
    }
}