<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Review;
use App\Model\Entity\Tag;
use App\Model\Entity\User;
use App\Model\Entity\VideoGame;
use App\Rating\CalculateAverageRating;
use App\Rating\CountRatingsPerValue;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

final class VideoGameFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly Generator $faker,
        private readonly CalculateAverageRating $calculateAverageRating,
        private readonly CountRatingsPerValue $countRatingsPerValue
    ) {}

    public function load(ObjectManager $manager): void
    {
        $users = $manager->getRepository(User::class)->findAll();

        // Création des tags
        $tagNames = [
            'Action',
            'Aventure',
            'RPG',
            'FPS',
            'Stratégie',
            'Simulation',
            'Sport',
            'Course',
            'Horreur',
            'Plateforme',
            'Puzzle',
            'Indépendant',
            'Multijoueur',
            'Monde ouvert',
            'Narratif',
        ];

        $tags = [];
        foreach ($tagNames as $tagName) {
            $tag = (new Tag())->setName($tagName);
            $manager->persist($tag);
            $tags[] = $tag;
        }

        $manager->flush();

        // Données réalistes de jeux vidéo
        $gamesData = [
            [
                'title' => 'The Witcher 3: Wild Hunt',
                'description' => "The Witcher 3: Wild Hunt est un RPG en monde ouvert développé par CD Projekt RED. Vous incarnez Geralt de Riv, un sorceleur à la recherche de sa fille adoptive Ciri, poursuivie par la Chasse Sauvage. Le jeu propose un monde immense et vivant, des quêtes secondaires d'une profondeur narrative exceptionnelle et un système de combat combinant magie et épée. Les choix du joueur influencent profondément l'histoire et le monde qui l'entoure.",
                'releaseDate' => '2015-05-19',
                'rating' => 5,
                'test' => "The Witcher 3 est un chef-d'œuvre absolu du jeu de rôle. CD Projekt RED a réussi l'exploit de créer un monde ouvert où chaque recoin recèle une histoire passionnante. Les quêtes secondaires rivalisent avec les quêtes principales d'autres jeux, et le scénario principal est porté par des personnages attachants et complexes. Le système de combat, bien qu'imparfait, offre suffisamment de profondeur pour rester engageant sur les centaines d'heures de jeu disponibles. Les DLC Hearts of Stone et Blood and Wine sont des extensions exemplaires. Un incontournable.",
                'tags' => ['RPG', 'Monde ouvert', 'Aventure', 'Narratif'],
            ],
            [
                'title' => 'Elden Ring',
                'description' => "Elden Ring est un action-RPG en monde ouvert développé par FromSoftware en collaboration avec George R.R. Martin. Le jeu se déroule dans l'Entre-terre, un royaume brisé après la destruction de l'Anneau Ancien. Le joueur incarne un Sans-éclat qui doit collecter les fragments de l'Anneau pour devenir le Seigneur Ancien. Le titre combine l'exigence des Souls-like avec une exploration libre et organique d'un monde ouvert fascinant.",
                'releaseDate' => '2022-02-25',
                'rating' => 5,
                'test' => "FromSoftware repousse ses propres limites avec Elden Ring. Le passage au monde ouvert est une réussite totale : l'exploration est constamment récompensée, les donjons optionnels sont brillamment conçus et les boss sont parmi les meilleurs de l'histoire du studio. La collaboration avec George R.R. Martin apporte une mythologie riche et complexe. La difficulté reste exigeante mais l'ouverture du monde permet toujours de trouver une alternative. Un accomplissement monumental dans le genre action-RPG.",
                'tags' => ['RPG', 'Action', 'Monde ouvert'],
            ],
            [
                'title' => 'The Legend of Zelda: Tears of the Kingdom',
                'description' => "Tears of the Kingdom est la suite directe de Breath of the Wild. Link se réveille dans un Hyrule transformé, avec de nouvelles îles célestes et des profondeurs souterraines à explorer. Le jeu introduit de nouvelles capacités comme Emprise, Fusion, Rétrospective et Infiltration, offrant une liberté créative sans précédent pour résoudre les énigmes et affronter les ennemis.",
                'releaseDate' => '2023-05-12',
                'rating' => 5,
                'test' => "Nintendo réinvente à nouveau la formule Zelda avec un système de construction et de physique qui pousse la créativité du joueur à son maximum. Chaque problème a des dizaines de solutions possibles, et le monde d'Hyrule n'a jamais été aussi dense et vertical. Les sanctuaires, les donjons et les quêtes secondaires sont variés et inventifs. Un titre qui repousse les limites du game design en monde ouvert.",
                'tags' => ['Aventure', 'Monde ouvert', 'Puzzle', 'Action'],
            ],
            [
                'title' => 'Red Dead Redemption 2',
                'description' => "Red Dead Redemption 2 est un western en monde ouvert développé par Rockstar Games. Le jeu suit Arthur Morgan, hors-la-loi et membre du gang de Dutch van der Linde, alors que le groupe fuit la loi à travers l'Amérique de 1899. Un récit épique sur la loyauté, la rédemption et la fin d'une époque.",
                'releaseDate' => '2018-10-26',
                'rating' => 5,
                'test' => "Rockstar livre un monde d'une densité et d'un réalisme inégalés. Chaque interaction, chaque animal, chaque coucher de soleil respire l'authenticité. L'histoire d'Arthur Morgan est l'une des plus poignantes du jeu vidéo, portée par des performances d'acteurs exceptionnelles. Le rythme lent ne conviendra pas à tous, mais ceux qui s'y plongent découvriront une expérience narrative inoubliable. Le multijoueur Red Dead Online, bien que prometteur, n'a malheureusement pas reçu le soutien qu'il méritait.",
                'tags' => ['Action', 'Aventure', 'Monde ouvert', 'Narratif'],
            ],
            [
                'title' => 'Baldur\'s Gate 3',
                'description' => "Baldur's Gate 3 est un RPG au tour par tour développé par Larian Studios, basé sur les règles de Donjons & Dragons 5e édition. Infecté par un parasite mind flayer, votre personnage doit trouver un remède tout en naviguant dans un monde riche en choix et conséquences. Le jeu propose une coopération jusqu'à 4 joueurs et une liberté d'approche quasi totale.",
                'releaseDate' => '2023-08-03',
                'rating' => 5,
                'test' => "Larian Studios livre le RPG de la décennie. La fidélité aux règles D&D 5e, combinée à une liberté d'action vertigineuse, crée des moments uniques à chaque partie. Les compagnons sont magnifiquement écrits, les quêtes regorgent de rebondissements, et le système de combat au tour par tour est tactiquement profond. L'Acte 3 souffre de quelques baisses de rythme, mais l'ensemble reste un monument du genre. Le mode coopératif ajoute une dimension hilarante et chaotique.",
                'tags' => ['RPG', 'Stratégie', 'Multijoueur', 'Narratif'],
            ],
            [
                'title' => 'Hades',
                'description' => "Hades est un roguelike d'action développé par Supergiant Games. Vous incarnez Zagreus, fils d'Hadès, qui tente de s'échapper des Enfers pour rejoindre le Mont Olympe. Chaque tentative de fuite offre de nouvelles armes, bénédictions des dieux et révélations narratives. Le jeu mêle brillamment action frénétique et progression narrative.",
                'releaseDate' => '2020-09-17',
                'rating' => 5,
                'test' => "Hades résout le problème fondamental du roguelike : la répétition. Grâce à un système narratif qui progresse à chaque mort, chaque run a un sens. Le gameplay est précis, nerveux et incroyablement satisfaisant. Les bénédictions des dieux olympiens créent des synergies passionnantes et chaque arme offre un style de jeu distinct. La direction artistique et la bande-son sont sublimes. Un jeu parfait dans son genre.",
                'tags' => ['Action', 'Indépendant', 'Narratif'],
            ],
            [
                'title' => 'Hollow Knight',
                'description' => "Hollow Knight est un metroidvania développé par Team Cherry. Vous explorez Hallownest, un vaste royaume d'insectes souterrain, armé d'une aiguille et de sorts. Le jeu propose une exploration non-linéaire, des combats exigeants et une atmosphère mélancolique unique. Plus de 40 heures de contenu pour les complétistes.",
                'releaseDate' => '2017-02-24',
                'rating' => 5,
                'test' => "Hollow Knight est l'un des meilleurs metroidvanias jamais créés. L'exploration d'Hallownest est captivante grâce à un level design interconnecté brillant et une atmosphère envoûtante. Les combats de boss sont exigeants mais justes, et le charme system offre une personnalisation bienvenue. Le contenu gratuit ajouté post-lancement est d'une générosité rare. Un jeu indépendant qui rivalise avec les plus grands.",
                'tags' => ['Plateforme', 'Indépendant', 'Action', 'Aventure'],
            ],
            [
                'title' => 'God of War Ragnarök',
                'description' => "God of War Ragnarök est un action-aventure développé par Santa Monica Studio. Suite directe de God of War (2018), le jeu suit Kratos et son fils Atreus alors que le Ragnarök approche. Le duo voyage à travers les neuf royaumes de la mythologie nordique pour affronter leur destin.",
                'releaseDate' => '2022-11-09',
                'rating' => 4,
                'test' => "Santa Monica Studio livre une suite ambitieuse qui développe magistralement la relation entre Kratos et Atreus. Le combat est plus varié et fluide que jamais, les royaumes sont plus nombreux et diversifiés. Le récit aborde des thèmes matures avec finesse. On regrette cependant quelques passages en longueur et une structure parfois trop linéaire comparée aux promesses d'exploration. Un excellent jeu qui peine légèrement à surpasser son prédécesseur.",
                'tags' => ['Action', 'Aventure', 'Narratif'],
            ],
            [
                'title' => 'Celeste',
                'description' => "Celeste est un jeu de plateforme développé par Maddy Makes Games. Vous incarnez Madeline qui tente d'escalader la montagne Celeste tout en affrontant ses démons intérieurs. Un jeu exigeant mais accessible grâce à son mode assist, avec un message fort sur la santé mentale et la persévérance.",
                'releaseDate' => '2018-01-25',
                'rating' => 5,
                'test' => "Celeste est bien plus qu'un jeu de plateforme difficile. C'est une œuvre touchante sur l'anxiété et la détermination, enveloppée dans un gameplay millimétré d'une précision absolue. Chaque écran est un puzzle d'exécution parfaitement calibré. La bande-son de Lena Raine est magistrale et le mode assist permet à tous de vivre cette histoire sans frustration. Les B-Sides et C-Sides offrent un défi supplémentaire pour les joueurs aguerris.",
                'tags' => ['Plateforme', 'Indépendant', 'Narratif'],
            ],
            [
                'title' => 'Cyberpunk 2077',
                'description' => "Cyberpunk 2077 est un RPG en monde ouvert développé par CD Projekt RED, situé dans Night City, une mégalopole obsédée par le pouvoir et la modification corporelle. Vous incarnez V, un mercenaire à la recherche d'un implant unique qui offre l'immortalité. Après un lancement catastrophique, le jeu a été considérablement amélioré par de nombreuses mises à jour.",
                'releaseDate' => '2020-12-10',
                'rating' => 4,
                'test' => "Après des débuts désastreux, Cyberpunk 2077 est devenu un excellent RPG grâce aux nombreux patchs et à l'extension Phantom Liberty. Night City est l'une des villes les plus impressionnantes jamais modélisées dans un jeu vidéo. Les quêtes principales et secondaires sont remarquablement écrites, et le gameplay a gagné en profondeur. Il reste des vestiges de la vision originale tronquée, mais le jeu dans son état actuel mérite amplement l'attention.",
                'tags' => ['RPG', 'Action', 'Monde ouvert', 'FPS'],
            ],
            [
                'title' => 'Disco Elysium',
                'description' => "Disco Elysium est un RPG narratif développé par ZA/UM. Vous incarnez un détective amnésique qui doit résoudre un meurtre dans un quartier portuaire délabré. Sans aucun combat, le jeu repose entièrement sur les dialogues, les compétences et les choix du joueur. Vos 24 compétences représentent différentes facettes de votre psyché.",
                'releaseDate' => '2019-10-15',
                'rating' => 5,
                'test' => "Disco Elysium est une révolution dans le RPG. L'écriture est d'une qualité littéraire rare dans le jeu vidéo, alternant humour noir, philosophie et émotion brute. Le système de compétences qui personnifient votre esprit est génial et crée des dialogues internes fascinants. L'absence de combat est compensée par une tension narrative constante. The Final Cut ajoute un doublage complet qui magnifie encore l'expérience. Un chef-d'œuvre intellectuel.",
                'tags' => ['RPG', 'Narratif', 'Indépendant'],
            ],
            [
                'title' => 'Resident Evil 4 Remake',
                'description' => "Resident Evil 4 Remake est la refonte moderne du classique de 2005, développée par Capcom. Leon S. Kennedy est envoyé en Espagne pour retrouver la fille du président, kidnappée par une secte mystérieuse. Le remake modernise les contrôles, les graphismes et le level design tout en conservant l'essence de l'original.",
                'releaseDate' => '2023-03-24',
                'rating' => 4,
                'test' => "Capcom prouve une fois de plus son savoir-faire en matière de remake. RE4 Remake modernise un classique sans le trahir : les contrôles sont fluides, les environnements magnifiquement repensés et la tension est palpable. L'ajout du système de furtivité et les modifications narratives sont bienvenues. La section de l'île reste le point faible du jeu mais elle a été considérablement améliorée. Un modèle de remake respectueux et ambitieux.",
                'tags' => ['Action', 'Horreur', 'Aventure'],
            ],
            [
                'title' => 'Stardew Valley',
                'description' => "Stardew Valley est un simulateur de ferme développé par ConcernedApe. Vous héritez de la ferme de votre grand-père dans la vallée de Stardew et devez la remettre en état. Cultiver, élever des animaux, pêcher, miner, cuisiner et tisser des liens avec les villageois : le jeu offre une boucle de gameplay addictive et relaxante.",
                'releaseDate' => '2016-02-26',
                'rating' => 5,
                'test' => "Stardew Valley est le jeu cozy par excellence. Développé par une seule personne, il offre un contenu colossal et une rejouabilité infinie. La boucle de gameplay est parfaitement calibrée : chaque saison apporte son lot de nouveautés et d'objectifs. Les villageois ont des personnalités attachantes et les mises à jour gratuites continuent d'enrichir l'expérience des années après sa sortie. Un jeu qui incarne la passion de son créateur.",
                'tags' => ['Simulation', 'Indépendant', 'Multijoueur'],
            ],
            [
                'title' => 'Sekiro: Shadows Die Twice',
                'description' => "Sekiro est un action-aventure développé par FromSoftware. Dans le Japon de l'ère Sengoku, vous incarnez le Loup, un shinobi au bras prothétique qui doit sauver son jeune seigneur. Le jeu privilégie un système de parades et de posture unique, abandonnant les mécaniques RPG des Souls pour une approche plus axée sur l'action pure.",
                'releaseDate' => '2019-03-22',
                'rating' => 5,
                'test' => "Sekiro est le jeu le plus exigeant de FromSoftware mais aussi le plus gratifiant. Le système de deflection transforme chaque combat en duel tendu et chorégraphié. Les outils prothétiques ajoutent une variété tactique bienvenue. Le level design vertical exploite brillamment le grappin. L'absence de builds et de coopération divise, mais la pureté de l'expérience en fait un chef-d'œuvre d'action. Le combat contre Isshin reste l'un des meilleurs boss de l'histoire du jeu vidéo.",
                'tags' => ['Action', 'Aventure'],
            ],
            [
                'title' => 'Animal Crossing: New Horizons',
                'description' => "Animal Crossing: New Horizons est un simulateur de vie développé par Nintendo. Vous arrivez sur une île déserte et devez la transformer en communauté prospère. Collecte d'insectes, pêche, décoration, jardinage et vie sociale avec des villageois animaux : le jeu se déroule en temps réel et au rythme du joueur.",
                'releaseDate' => '2020-03-20',
                'rating' => 4,
                'test' => "Sorti au moment parfait (début du confinement mondial), Animal Crossing: New Horizons est devenu un phénomène culturel. La personnalisation de l'île est poussée à l'extrême et la boucle quotidienne est satisfaisante. Le système de crafting divise mais le contenu saisonnier et les mises à jour ont enrichi l'expérience. On regrette un rythme de mises à jour lent au lancement et certaines régressions par rapport à New Leaf. Un jeu relaxant et charmant.",
                'tags' => ['Simulation', 'Aventure'],
            ],
            [
                'title' => 'DOOM Eternal',
                'description' => "DOOM Eternal est un FPS développé par id Software. Le Doom Slayer poursuit sa croisade contre les forces de l'Enfer qui ont envahi la Terre. Plus rapide, plus mobile et plus exigeant que DOOM 2016, le jeu est un ballet de violence chorégraphié avec précision.",
                'releaseDate' => '2020-03-20',
                'rating' => 4,
                'test' => "DOOM Eternal est le FPS en arène poussé à sa quintessence. Chaque combat est un puzzle de gestion de ressources à résoudre à toute vitesse : glory kills pour la santé, tronçonneuse pour les munitions, lance-flammes pour l'armure. Le level design est varié et les combats de boss impressionnants. Les phases de plateforme divisent mais rythment bien l'action. Le DLC The Ancient Gods pousse le défi encore plus loin. Un FPS magistral pour les amateurs de sensations fortes.",
                'tags' => ['FPS', 'Action'],
            ],
            [
                'title' => 'Civilization VI',
                'description' => "Civilization VI est un jeu de stratégie au tour par tour développé par Firaxis Games. Guidez une civilisation de l'âge de pierre à l'ère spatiale. Recherche technologique, diplomatie, guerre, culture : chaque partie est unique grâce aux multiples conditions de victoire et à la génération procédurale des cartes.",
                'releaseDate' => '2016-10-21',
                'rating' => 4,
                'test' => "Civilization VI est la quintessence du 'encore un tour'. Le système de districts apporte une dimension spatiale stratégique bienvenue et les civilisations sont plus asymétriques que jamais. Les extensions Gathering Storm et Rise & Fall ajoutent des couches de profondeur essentielles. L'IA reste le talon d'Achille de la série, surtout en diplomatie. Avec les mods et les centaines d'heures de contenu, c'est le jeu de stratégie de référence de cette génération.",
                'tags' => ['Stratégie', 'Simulation', 'Multijoueur'],
            ],
            [
                'title' => 'It Takes Two',
                'description' => "It Takes Two est un jeu d'aventure coopératif développé par Hazelight Studios. Cody et May, un couple au bord du divorce, sont transformés en poupées par leur fille. Ils doivent traverser un monde fantastique pour retrouver leur taille normale. Le jeu se joue exclusivement en coopération locale ou en ligne.",
                'releaseDate' => '2021-03-26',
                'rating' => 4,
                'test' => "It Takes Two est une célébration de la coopération. Josef Fares et son équipe réinventent constamment le gameplay : chaque chapitre introduit de nouvelles mécaniques qui ne servent qu'une fois. Cette générosité créative est impressionnante et maintient l'intérêt sur toute la durée. L'histoire du couple est parfois maladroite mais la variété du gameplay compense largement. Le meilleur jeu coopératif de sa génération, un argument imparable pour jouer à deux.",
                'tags' => ['Aventure', 'Plateforme', 'Multijoueur'],
            ],
            [
                'title' => 'Returnal',
                'description' => "Returnal est un roguelike TPS développé par Housemarque. L'astronaute Selene s'écrase sur la planète Atropos et se retrouve piégée dans une boucle temporelle. Chaque mort la ramène à son vaisseau avec un monde reconfiguré. Le jeu mêle exploration, combat intense et narration environnementale cryptique.",
                'releaseDate' => '2021-04-30',
                'rating' => 4,
                'test' => "Returnal prouve que le AAA et le roguelike peuvent coexister harmonieusement. Le gameplay est d'une précision chirurgicale, les armes satisfaisantes et la difficulté bien dosée. L'atmosphère alien est captivante et la narration fragmentée intrigue. Le manque de sauvegarde mid-run au lancement était frustrant (corrigé depuis). La durée des runs et le facteur chance peuvent décourager, mais la boucle de gameplay est irréprochable.",
                'tags' => ['Action', 'FPS', 'Horreur'],
            ],
            [
                'title' => 'FIFA 23',
                'description' => "FIFA 23 est le dernier jeu de football sous licence FIFA développé par EA Sports. Le titre inclut pour la première fois le football féminin en clubs et propose les modes Carrière, Ultimate Team et VOLTA Football. La technologie HyperMotion2 capture des milliers d'animations réalistes.",
                'releaseDate' => '2022-09-30',
                'rating' => 3,
                'test' => "FIFA 23 marque la fin d'une ère avant le passage à EA Sports FC. Le gameplay est le plus réaliste de la série grâce à HyperMotion2, avec des accélérations et des tirs qui sonnent juste. L'ajout des clubs féminins est bienvenu. Cependant, Ultimate Team reste un gouffre à microtransactions, le mode Carrière évolue trop peu et les serveurs en ligne sont parfois instables. Un jeu compétent mais qui peine à se réinventer au-delà du terrain.",
                'tags' => ['Sport', 'Multijoueur', 'Simulation'],
            ],
        ];

        // Création des jeux vidéo
        $videoGames = [];
        foreach ($gamesData as $data) {
            $videoGame = (new VideoGame())
                ->setTitle($data['title'])
                ->setDescription($data['description'])
                ->setReleaseDate(new DateTimeImmutable($data['releaseDate']))
                ->setTest($data['test'])
                ->setRating($data['rating'])
                ->setImageName(null)
                ->setImageSize(null);

            // Ajout des tags
            foreach ($data['tags'] as $tagName) {
                $matchingTag = array_filter($tags, fn(Tag $t) => $t->getName() === $tagName);
                if (!empty($matchingTag)) {
                    $videoGame->getTags()->add(reset($matchingTag));
                }
            }

            $manager->persist($videoGame);
            $videoGames[] = $videoGame;
        }

        $manager->flush();

        // Ajout de reviews réalistes
        $reviewComments = [
            5 => [
                "Un chef-d'œuvre absolu, je n'ai pas pu lâcher la manette !",
                "Rarement un jeu m'a autant captivé. Une expérience inoubliable.",
                "Tout est parfait : le gameplay, l'histoire, la direction artistique.",
                "Mon jeu de l'année sans hésitation. Foncez !",
                "Incroyable du début à la fin, j'en redemande.",
            ],
            4 => [
                "Excellent jeu avec quelques défauts mineurs qui n'entachent pas l'expérience.",
                "Très bon moment passé, il manque juste un petit quelque chose pour la perfection.",
                "Solide sur tous les aspects, je recommande vivement.",
                "Un très bon cru, même si on sent du potentiel inexploité.",
                "Prenant et bien réalisé, quelques longueurs cependant.",
            ],
            3 => [
                "Correct sans être transcendant. On passe un moment agréable.",
                "Des bonnes idées mais une exécution inégale.",
                "Sympa pour passer le temps mais vite oublié.",
                "Le jeu a des qualités mais aussi des défauts qui gâchent l'expérience.",
                "Moyen. Attendez une promotion pour l'acheter.",
            ],
            2 => [
                "Décevant au vu des promesses. Beaucoup de bugs et de contenu manquant.",
                "Je m'attendais à mieux. Le gameplay est répétitif.",
                "Pas terrible, j'ai abandonné après quelques heures.",
            ],
            1 => [
                "Une catastrophe. Injouable en l'état.",
                "Je regrette mon achat. Rien ne fonctionne correctement.",
            ],
        ];

        foreach ($videoGames as $videoGame) {
            // Chaque jeu reçoit entre 3 et 8 reviews
            $numReviews = random_int(3, min(8, count($users)));
            $reviewers = (array) array_rand(array_flip(array_map(fn($u) => array_search($u, $users), $users)), $numReviews);

            foreach ($reviewers as $userIndex) {
                // Pondération des notes selon la note éditoriale
                $baseRating = $videoGame->getRating();
                $deviation = random_int(-1, 1);
                $userRating = max(1, min(5, $baseRating + $deviation));

                $comments = $reviewComments[$userRating];
                $comment = $comments[array_rand($comments)];

                $review = (new Review())
                    ->setVideoGame($videoGame)
                    ->setUser($users[$userIndex])
                    ->setRating($userRating)
                    ->setComment($comment);

                $manager->persist($review);
            }
        }

        $manager->flush();

        // Recalcul des moyennes et distributions
        foreach ($videoGames as $videoGame) {
            $this->calculateAverageRating->calculateAverage($videoGame);
            $this->countRatingsPerValue->countRatingsPerValue($videoGame);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
