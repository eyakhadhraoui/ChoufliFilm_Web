<?php



// src/Service/SentimentService.php

namespace App\Service;

use Sentiment\Analyzer;
use App\Entity\Reclamation;

class SentimentService
{
    public function getSentimentScore(string $text): array
    {
        $analyzer = new Analyzer();
        return $analyzer->getSentiment($text);
    }

    public function setReclamationPriority(Reclamation $reclamation): void
    {
        $score = $this->getSentimentScore($reclamation->getDescription());

        if ($score['compound'] <= -0.5) {
            $reclamation->setPriority('High');
        } elseif ($score['compound'] >= 0.5) {
            $reclamation->setPriority('Low');
        } else {
            $reclamation->setPriority('Medium');
        }
    }
}
