<?php

declare(strict_types=1);

namespace App\Tests\Functional;

final class NavigationTest extends FunctionalTestCase
{
    /**
    * @dataProvider navigationLinksProvider
    */
    public function testNavigationLinks(string $linkText, string $expectedSelector, ?string $expectedText = null): void
    {
        $crawler = $this->get('/');
        $link = $crawler->selectLink($linkText)->link();
        $this->client->click($link);

        $this->assertResponseIsSuccessful();
        if ($expectedText !== null) {
            $this->assertSelectorTextContains($expectedSelector, $expectedText);
        } else {
            $this->assertSelectorExists($expectedSelector);
        }
    }

    public static function navigationLinksProvider(): \Generator
    {
        yield 'login' => [
            'linkText' => 'Se connecter',
            'expectedSelector' => 'form[name="login"]',
            'expectedText' => null
        ];
        yield 'register' => [
            'linkText' => 'S\'inscrire',
            'expectedSelector' => 'form[name="register"]',
            'expectedText' => null
        ];
        yield 'home' => [
            'linkText' => 'Jeux vidéo',
            'expectedSelector' => 'form[name="sorting"]',
            'expectedText' => null
        ];
    }

    public function testClickOnGameFromListGoesToDetailPage(): void
    {
        // 1. Accéder à la page de liste des jeux
        $crawler = $this->get('/');

        // 2. Cliquer sur le lien du premier jeu
        $link = $crawler->filter('.game-card a')->first()->link();
        // Récupérer le titre du jeu pour vérifier que nous sommes sur la bonne page après le clic
        $gameTitle = $crawler->filter('.game-card-title')->first()->text();
        $crawler = $this->client->click($link);

        // 3. Vérifier que nous sommes sur la page de détail du jeu
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $gameTitle);
    }

    public function testPaginationLinksNavigateCorrectly(): void
    {
        // 1. Accéder à la page de liste des jeux
        $crawler = $this->get('/');

        // 2. Cliquer sur le lien de la page 2
        $link = $crawler->selectLink('2')->link();
        $crawler = $this->client->click($link);

        // 3. Vérifier que nous sommes sur la page 2 (en vérifiant la présence d'un élément spécifique à cette page)
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('page=2', $this->client->getRequest()->getUri());    
    }
}
