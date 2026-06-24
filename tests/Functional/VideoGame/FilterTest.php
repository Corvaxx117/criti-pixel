<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Model\Entity\Tag;
use App\Tests\Functional\FunctionalTestCase;

final class FilterTest extends FunctionalTestCase
{
    /**
     * @dataProvider filterByTagsProvider
     * @param string[] $tagNames
     */
    public function testFilterByTags(array $tagNames, int $expectedCount): void
    {
        // TO DO : Autre approche plus cohérente :
        // page d'accueil, simuler clic sur les tags, submit du formulaire de filtrage, asserter le nombre de résultats

        // 1. Récupérer les ids des tags par leur nom
        $em = $this->getEntityManager();
        $tagIds = [];
        foreach ($tagNames as $name) {
            $tag = $em->getRepository(Tag::class)->findOneBy(['name' => $name]);
            assert($tag !== null);
            $tagIds[] = $tag->getId();
        }
        // 2. transforme un tableau d'ids PHP en query string HTTP valide
        $queryString = implode('&', array_map(fn($id) => 'filter[tags][]=' . $id, $tagIds));
        $this->get('/?' . $queryString); // /?filter[tags][]=3&filter[tags][]=14
        // 3. Asserter le nombre de résultats
        self::assertResponseIsSuccessful();
        self::assertSelectorCount($expectedCount, 'article.game-card');
    }

    public static function filterByTagsProvider(): \Generator
    {
        yield 'aucun tag' => [
            [],
             10
        ];
        yield 'un tag RPG' => [
            ['RPG'],
            5
        ];
        yield 'deux tags RPG + Monde ouvert' => [
            ['RPG', 'Monde ouvert'],
            3
        ];
        yield 'tag sans jeu' => [
            ['Course'],
            0
        ];
    }

    public function testShouldListTenVideoGames(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');
        $this->client->clickLink('2');
        self::assertResponseIsSuccessful();
    }

    public function testShouldFilterVideoGamesBySearch(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');
        $this->client->submitForm('Filtrer', ['filter[search]' => 'Baldur\'s Gate 3'], 'GET');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(1, 'article.game-card');
    }
}
