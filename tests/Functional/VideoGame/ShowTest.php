<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Model\Entity\VideoGame;
use App\Tests\Functional\FunctionalTestCase;

final class ShowTest extends FunctionalTestCase
{
    public function testShouldShowVideoGame(): void
    {
        $videoGame = $this->getEntityManager()->getRepository(VideoGame::class)->findOneBy([]);
        assert($videoGame !== null);
        $this->get('/' . $videoGame->getSlug());
        self::assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $videoGame->getTitle());
    }
}
