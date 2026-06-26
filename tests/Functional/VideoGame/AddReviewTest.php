<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Tests\Functional\FunctionalTestCase;

final class AddReviewTest extends FunctionalTestCase
{
    public function testCreateReviewSuccess(): void
    {
        // 1. Se connecter en tant qu'utilisateur
        $this->login('testeur@critipixel.fr');
        // 2. Accéder à la page du jeu
        $crawler = $this->get('/the-witcher-3-wild-hunt');
        // 3. Vérifier le nombre de reviews avant de soumettre le formulaire
        $reviewCountBefore = count($crawler->filter('#pane-reviews .list-group .list-group-item'));
        // 4. Soumettre le formulaire d'ajout de review
        $this->client->submitForm('Poster', [
            'review[rating]' => 5,
            'review[comment]' => 'Excellent jeu !'
        ]);
        $this->assertResponseStatusCodeSame(302);
        // 5. Vérifier que la review a été ajoutée et que la note moyenne a été mise à jour
        $crawler = $this->client->followRedirect();
        $reviewCountAfter = count($crawler->filter('#pane-reviews .list-group .list-group-item'));

        $this->assertSame($reviewCountBefore + 1, $reviewCountAfter);
        // 6. Vérifier que la nouvelle review est ajoutée par l'utilisateur "testeur" et affichée avec les bonnes informations
        $newReview = $crawler->filter('#pane-reviews .list-group-item:contains("testeur")');
        $this->assertSame('5', $newReview->filter('.value')->text());
        $this->assertStringContainsString('Excellent jeu !', $newReview->filter('p')->text());
    }

    public function testCannotCreateReviewWhenNotAuthenticated(): void
    {
        // 1. Accéder à la page du jeu
        $this->get('/the-witcher-3-wild-hunt');
        // 2. Vérifier que le formulaire d'ajout de review n'est pas affiché
        self::assertSelectorNotExists('form[name="review"]');
    }

    public function testShouldNotAddReviewWhenAlreadyReviewed(): void
    {
        // 1. Se connecter en tant qu'utilisateur
        $this->login('testeur@critipixel.fr');
        // 2. Accéder à la page du jeu
        $this->get('/the-witcher-3-wild-hunt');
        // 3. Soumettre le formulaire d'ajout de review
        $this->client->request('POST', '/the-witcher-3-wild-hunt', [
            'review' => [
                'rating' => 5,
                'comment' => 'Excellent jeu !'
            ]
        ]);
        $this->assertResponseStatusCodeSame(422);
    }

    public function testShouldReturn422WhenRatingIsMissing(): void
    {
        $this->login('testeur@critipixel.fr');
        $crawler = $this->get('/the-witcher-3-wild-hunt');
        $form = $crawler->selectButton('Poster')->form();
        $form->disableValidation();
        $form['review[rating]'] = '';
        // Ne pas setter review[rating] → il sera absent
        $this->client->submit($form);   
        $this->assertResponseStatusCodeSame(422);
    }

    public function testShouldReturn422WhenUnauthenticatedUserPostsReview(): void
    {
        // Ici on ne peut pas tester une 401 car le formulaire n'est pas affiché, donc on simule la requête POST directement
        // Il faudrait désolidariser la validation du formulaire de la validation du droit d'ajouter une review pour pouvoir tester une 401, mais ce n'est pas le cas actuellement
        // Ainsi on isolerait la validation du formulaire (422) de la validation du droit d'ajouter une review (401)
        // Mais 401 ne peut pas retourné car on est automatiquement redirigé vers la page de login avant même d'atteindre la validation du formulaire, donc on teste une 422 qui est actuellement retournée
        $this->get('/the-witcher-3-wild-hunt');
        $this->client->request('POST', '/the-witcher-3-wild-hunt', [
            'review' => [
                'rating' => 5,
                'comment' => 'Excellent jeu !'
            ]
        ]);
        $this->assertResponseStatusCodeSame(422);
    }

    public function testShouldReturn422WhenFormIsSubmittedWithoutCSRFToken(): void
    {
        $this->login('testeur@critipixel.fr');
        $this->client->request('POST', '/the-witcher-3-wild-hunt', [
            'review' => [
                'rating' => 5,
                'comment' => 'Excellent jeu !'
                // pas de _token → CSRF invalide
            ]
        ]);
        $this->assertResponseStatusCodeSame(422);
    }
}
