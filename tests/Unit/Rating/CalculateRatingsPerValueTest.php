<?php

declare(strict_types=1);

namespace App\Tests\Unit\Rating;

use PHPUnit\Framework\TestCase;
use App\Model\Entity\VideoGame;
use App\Model\Entity\Review;
use App\Model\Entity\User;
use App\Rating\RatingHandler;

class CalculateRatingsPerValueTest extends TestCase
{
    private RatingHandler $ratingHandler;

    protected function setUp(): void
    {
        $this->ratingHandler = new RatingHandler();
    }

    /**
     * @dataProvider ratesProvider
     * @param VideoGame $videoGame
     * @param array<int, int> $expectedValues
     */
    public function testRatingPerValueCalculation(VideoGame $videoGame, array $expectedValues): void
    {
        $this->ratingHandler->countRatingsPerValue($videoGame);
        $numberOfRatingsPerValue = $videoGame->getNumberOfRatingsPerValue();

        $this->assertSame($expectedValues[1], $numberOfRatingsPerValue->getNumberOfOne());
        $this->assertSame($expectedValues[2], $numberOfRatingsPerValue->getNumberOfTwo());
        $this->assertSame($expectedValues[3], $numberOfRatingsPerValue->getNumberOfThree());
        $this->assertSame($expectedValues[4], $numberOfRatingsPerValue->getNumberOfFour());
        $this->assertSame($expectedValues[5], $numberOfRatingsPerValue->getNumberOfFive());    
    }

    /**
     * Fournit des cas de test pour la méthode testRatingPerValueCalculation.
     *
     * @return iterable<string, array{videoGame: VideoGame, ExpectedValues: array<int, int>}>
     */
    public static function ratesProvider(): iterable
    {
        $rates = [
            1, 1, 1,
            2, 
            3, 3, 3, 3,
            4, 4,   
            5, 5, 5, 5, 5 
        ];
        $videoGame = self::createVideoGameWithRatings($rates);

        yield 'Ratings with multiple reviews' => [
            'videoGame' => $videoGame,
            'ExpectedValues' => [
                1 => 3,
                2 => 1,
                3 => 4,
                4 => 2,
                5 => 5
            ],
        ];
        
        // Cas avec aucune review : tous les compteurs doivent être à 0
        yield 'Ratings with no reviews' => [
            'videoGame' => new VideoGame(),
            'ExpectedValues' => [
                1 => 0,
                2 => 0,
                3 => 0,
                4 => 0,
                5 => 0
            ],
        ];
    }

    /**
     * Crée un objet VideoGame avec des reviews ayant les notes spécifiées.
     *
     * @param array<int> $ratings
     * @return VideoGame
     */
    private static function createVideoGameWithRatings(array $ratings): VideoGame
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
