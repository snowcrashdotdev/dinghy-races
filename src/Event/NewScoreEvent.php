<?php
namespace App\Event;

use App\Entity\Score;
use Symfony\Component\EventDispatcher\Event;

class NewScoreEvent extends Event
{
    public const NAME = 'score.new';

    protected $score;

    public function __construct(Score $score)
    {
        $this->score = $score;
    }

    public function getScore()
    {
        return $this->score;
    }
}