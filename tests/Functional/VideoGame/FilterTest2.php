<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Model\Entity\Tag;
use App\Tests\Functional\FunctionalTestCase;

final class FilterTest2 extends FunctionalTestCase
{
    /**
     * @dataProvider filterByTagsProvider
     * @param string[] $tagNames
     */
    public function testFilterByTags(array $tagNames, int $expectedCount): void
    {
        // 1. Charger la page d'accueil
        $crawler = $this->get('/');

        // 2. Récupérer le formulaire
        $form = $crawler->selectButton('Filtrer')->form();
        // désactiver la validation HTML5 pour pouvoir soumettre le formulaire même si les checkboxes ne sont pas cochées
        $form->disableValidation();

        // 3. Récupérer les ids des tags par leur nom
        $em = $this->getEntityManager();
        $tagIds = [];
        foreach ($tagNames as $name) {
            $tag = $em->getRepository(Tag::class)->findOneBy(['name' => $name]);
            assert($tag !== null);
            $tagIds[] = (string) $tag->getId();
        }

        // 4. Cocher les checkboxes correspondantes
        if ([] !== $tagIds) {
            $form->setValues(['filter[tags]' => $tagIds]);
        }

        // 5. Soumettre
        $this->client->submit($form);

        // 6. Asserter
        self::assertResponseIsSuccessful();
        self::assertSelectorCount($expectedCount, 'article.game-card');
    }

    /**
     * @return \Generator<string, array{string[], int}>
     */
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
}
