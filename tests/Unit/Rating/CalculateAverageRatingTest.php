<?php

declare(strict_types=1);

namespace App\Tests\Unit\Rating;

use PHPUnit\Framework\TestCase;
use App\Model\Entity\VideoGame;
use App\Model\Entity\Review;
use App\Model\Entity\User;
use App\Rating\RatingHandler;

class CalculateAverageRatingTest extends TestCase
{
    private RatingHandler $ratingHandler;

    protected function setUp(): void
    {
        $this->ratingHandler = new RatingHandler();
    }

    /**
     * @dataProvider ratesProvider
     */
    public function testAverageCalculation(array $rates, ?int $averageValue): void
    {
        $videoGame = $this->createVideoGameWithRatings($rates);

        $this->ratingHandler->calculateAverage($videoGame);

        $this->assertSame($averageValue, $videoGame->getAverageRating());
    }

    public static function ratesProvider(): iterable
    {
        // Cas nominal : plusieurs notes variées, somme=12, count=3, moyenne=4.0, ceil=4
        yield 'Average with multiple reviews' => [
            'rates' => [3, 4, 5],
            'averageValue' => 4,
        ];

        // Vérifie l'arrondi au supérieur : somme=7, count=2, moyenne=3.5, ceil=4
        yield 'Average rounds up (ceil)' => [
            'rates' => [3, 4],
            'averageValue' => 4,
        ];

        // Une seule note : la moyenne doit être égale à cette note
        yield 'Single review' => [
            'rates' => [5],
            'averageValue' => 5,
        ];

        // Aucune review : la moyenne doit être null
        yield 'No reviews returns null' => [
            'rates' => [],
            'averageValue' => null,
        ];

        // Toutes les notes identiques : la moyenne = cette note
        yield 'All same ratings' => [
            'rates' => [3, 3, 3, 3],
            'averageValue' => 3,
        ];

        // Toutes les notes au minimum (borne basse)
        yield 'Minimum ratings' => [
            'rates' => [1, 1, 1],
            'averageValue' => 1,
        ];

        // Toutes les notes au maximum (borne haute)
        yield 'Maximum ratings' => [
            'rates' => [5, 5, 5],
            'averageValue' => 5,
        ];

        // Mélange extrême : somme=6, count=2, moyenne=3.0, ceil=3
        yield 'Extreme spread' => [
            'rates' => [1, 5],
            'averageValue' => 3,
        ];
    }

    private function createVideoGameWithRatings(array $ratings): VideoGame
    {
        $videoGame = (new VideoGame())
            ->setTitle('Test Game')
            ->setDescription('Description de test')
            ->setReleaseDate(new \DateTimeImmutable());

        // Création d'un utilisateur fictif pour les reviews
        $user = new User();

        foreach ($ratings as $rating) {
            $videoGame->addReview(
                 (new Review())
                ->setUser($user)
                ->setRating($rating)
            );
        }

        return $videoGame;
    }
}
