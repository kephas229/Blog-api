<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── 1. Utilisateurs ──────────────────────────────────────────────────
        $admin = User::create([
            'name'     => 'Sophie Marchand',
            'email'    => 'admin@blogflow.fr',
            'password' => Hash::make('Admin@2024!'),
            'role'     => 'admin',
        ]);

        $authors = collect([
            ['name' => 'Thomas Leroy',    'email' => 'thomas.leroy@blogflow.fr',   'password' => Hash::make('Thomas@2024!'),  'role' => 'author'],
            ['name' => 'Camille Dubois',  'email' => 'camille.dubois@blogflow.fr', 'password' => Hash::make('Camille@2024!'), 'role' => 'author'],
            ['name' => 'Lucas Martin',    'email' => 'lucas.martin@blogflow.fr',   'password' => Hash::make('Lucas@2024!'),   'role' => 'author'],
            ['name' => 'Elodie Bernard',  'email' => 'elodie.bernard@blogflow.fr', 'password' => Hash::make('Elodie@2024!'),  'role' => 'author'],
        ])->map(fn($d) => User::create($d));

        $allAuthors = $authors->prepend($admin);

        // ─── 2. Catégories ────────────────────────────────────────────────────
        $cats = collect([
            'Développement Web',
            'Marketing Digital',
            'Design & UX',
            'Entrepreneuriat',
            'Intelligence Artificielle',
            'Cybersécurité',
            'Productivité',
        ])->mapWithKeys(fn($n) => [$n => Category::create(['name' => $n])]);

        // ─── 3. Articles + Commentaires ───────────────────────────────────────
        foreach ($this->getArticlesData() as $data) {
            $article = Article::create([
                'title'             => $data['title'],
                'short_description' => $data['short'],
                'content'           => $data['content'],
                'image'             => $data['image'],
                'user_id'           => $allAuthors->random()->id,
                'category_id'       => $cats[$data['cat']]->id,
            ]);

            foreach ($data['comments'] ?? [] as $c) {
                Comment::create([
                    'visitor_name'  => $c['name'],
                    'visitor_email' => $c['email'],
                    'message'       => $c['msg'],
                    'article_id'    => $article->id,
                ]);
            }
        }
    }


    private function getArticlesData(): array
    {
        return [

            // ══════════════════════════════════════════════════════════════════
            // DÉVELOPPEMENT WEB
            // ══════════════════════════════════════════════════════════════════
            [
                'cat'     => 'Développement Web',
                'title'   => 'Les fondamentaux de React 19 : ce qui change vraiment',
                'short'   => 'React 19 redéfinit la gestion des états, des actions serveur et de la mémoïsation. Tour complet des nouveautés avant de migrer vos projets.',
                'image'   => 'https://images.unsplash.com/photo-1633356122544-f134324a6cee?w=1200&auto=format&fit=crop',
                'content' => "React 19 est sans doute la mise à jour la plus significative depuis l'introduction des hooks. Elle redéfinit la façon dont nous gérons les états, les formulaires et les interactions côté serveur.\n\n## Les Actions : la fin des useState imbriqués\n\nJusqu'à présent, gérer un formulaire asynchrone nécessitait d'enchaîner plusieurs useState pour le chargement, les erreurs et les données. React 19 introduit les Actions, qui centralisent tout ce flux dans une seule fonction asynchrone.\n\nLe hook useActionState prend en charge automatiquement l'état pending, les erreurs et la réponse — sans orchestration manuelle.\n\n## Le compilateur React\n\nLe compilateur React (anciennement React Forget) est désormais intégré. Il analyse votre code à la compilation et applique automatiquement les optimisations que vous faisiez manuellement avec useMemo et useCallback. Vous n'avez plus à gérer les dépendances de mémoïsation.\n\n## use() : lire des promesses dans le rendu\n\nLa nouvelle fonction use() lit la valeur d'une Promise ou d'un Context directement dans le corps d'un composant. Couplée à Suspense, elle offre une expérience de chargement beaucoup plus fluide.\n\n## Conclusion\n\nReact 19 ne casse rien, mais ouvre une nouvelle façon de construire des interfaces. Commencez par migrer un petit projet pour prendre en main ces concepts avant de les déployer en production.",
                'comments' => [
                    ['name' => 'Maxime Fontaine',  'email' => 'maxime.f@gmail.com',     'msg' => "Le compilateur React m'a déjà sauvé la mise sur un projet Next.js. Plus besoin de se battre avec les dépendances manquantes de useMemo."],
                    ['name' => 'Inès Carpentier',  'email' => 'ines.c@protonmail.com',  'msg' => "La fonction use() avec Suspense est vraiment élégante. Ça simplifie énormément la gestion des données asynchrones."],
                ],
            ],
            [
                'cat'     => 'Développement Web',
                'title'   => 'Construire une API REST robuste avec Laravel 11',
                'short'   => 'De la conception des routes à la sécurisation avec Sanctum, ce guide couvre tout ce qu\'il faut pour livrer une API Laravel prête pour la production.',
                'image'   => 'https://images.unsplash.com/photo-1627398242454-45a1465c2479?w=1200&auto=format&fit=crop',
                'content' => "Laravel reste le framework PHP le plus populaire pour une bonne raison : il offre une expérience développeur exceptionnelle tout en étant puissant pour les projets à grande échelle.\n\n## La structure d'une API bien pensée\n\nUne bonne API REST respecte quelques principes fondamentaux : les ressources sont des noms (pas des verbes), les verbes HTTP portent l'action, les codes HTTP sont sémantiques.\n\n## Form Requests : la validation propre\n\nDéléguer la validation à des Form Requests est une décision d'architecture qui paie à long terme. Le controller reste lisible et les règles de validation sont testables indépendamment.\n\n## Sécurisation avec Sanctum\n\nPour une API consommée par un SPA, Sanctum est le choix idéal. Il émet des tokens personnels stockés en base de données, révocables à la demande.\n\n## API Resources\n\nLes API Resources définissent précisément ce que l'API expose, protégeant contre l'exposition accidentelle de champs sensibles.\n\n## Conseil final\n\nDocumentez votre API dès le début avec L5-Swagger. Une API sans documentation est difficile à maintenir.",
                'comments' => [
                    ['name' => 'Aurore Petit',  'email' => 'aurore.p@live.fr',    'msg' => "La partie sur les Form Requests m'a convaincu de refactoriser mes controllers. Tellement plus lisible."],
                    ['name' => 'Kévin Morel',   'email' => 'kmorel.dev@gmail.com', 'msg' => "J'ajouterais l'importance des tests de feature avec PHPUnit pour valider l'API avant chaque déploiement."],
                ],
            ],
            [
                'cat'     => 'Développement Web',
                'title'   => 'Tailwind CSS v4 : tout ce qui change dans la nouvelle version',
                'short'   => 'Tailwind v4 abandonne le fichier de configuration JavaScript au profit d\'une configuration CSS native. Voici ce que cela implique pour vos projets.',
                'image'   => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=1200&auto=format&fit=crop',
                'content' => "Tailwind CSS v4 marque un tournant dans la façon dont le framework est configuré. La philosophie reste la même — des classes utilitaires composables — mais l'infrastructure a été entièrement repensée.\n\n## Fin du tailwind.config.js\n\nLa configuration se fait désormais directement dans votre fichier CSS principal, via des variables et des directives CSS natives. Les tokens de design sont de vraies variables CSS.\n\n## Un moteur réécrit en Rust\n\nTailwind v4 utilise un nouveau moteur basé sur LightningCSS, environ 5 fois plus rapide. Sur de gros projets, les démarrages de serveur sont quasi-instantanés.\n\n## Les variants dynamiques\n\nIl est maintenant possible de créer des variants personnalisés à la volée directement dans les classes HTML.\n\n## Migration depuis v3\n\nTailwind fournit un outil de codemod qui transforme la majorité du code existant. Je recommande de migrer les nouveaux projets immédiatement.",
                'comments' => [
                    ['name' => 'Juliette Renaud', 'email' => 'juliette.r@yahoo.fr', 'msg' => "La config CSS-first est un vrai soulagement sur notre projet multi-frameworks."],
                    ['name' => 'Antoine Mercier', 'email' => 'a.mercier@hotmail.fr', 'msg' => "Sur notre monorepo de 200+ composants, le cold start est passé de 8s à moins de 2s. Bluffant."],
                ],
            ],
            [
                'cat'     => 'Développement Web',
                'title'   => 'Maîtriser TypeScript en 2025 : les patterns avancés',
                'short'   => 'Au-delà des types de base, TypeScript offre des outils puissants pour modéliser des domaines complexes. Generics, conditional types, template literals — guide complet.',
                'image'   => 'https://images.unsplash.com/photo-1516116216624-53e697fedbea?w=1200&auto=format&fit=crop',
                'content' => "TypeScript est devenu incontournable dans l'écosystème JavaScript. Mais beaucoup de développeurs s'arrêtent aux types de base. Voici les patterns avancés qui font la différence.\n\n## Generics : écrire du code réutilisable et typé\n\nLes generics permettent d'écrire des fonctions et des classes qui fonctionnent avec n'importe quel type tout en conservant la sécurité du typage. C'est la base de toute bibliothèque TypeScript bien conçue.\n\n## Conditional Types\n\nLes conditional types permettent de définir des types qui dépendent d'autres types, comme des expressions ternaires au niveau du système de types.\n\n## Template Literal Types\n\nDepuis TypeScript 4.1, les template literal types permettent de construire des types de chaînes complexes à partir de types plus simples. Utile pour typer des routes, des événements CSS, etc.\n\n## Mapped Types\n\nLes mapped types permettent de transformer les propriétés d'un type existant, créant des utilitaires comme Partial, Required, Readonly, Pick et Omit.\n\n## Conseil pratique\n\nN'utilisez pas les types avancés pour le plaisir. Chaque ajout de complexité doit répondre à un vrai besoin de sécurité ou d'expressivité.",
                'comments' => [
                    ['name' => 'Romain Gilles', 'email' => 'romain.g@outlook.fr', 'msg' => "Les conditional types m'ont débloqué sur un problème de typage que je n'arrivais pas à résoudre depuis des semaines."],
                ],
            ],

            // ══════════════════════════════════════════════════════════════════
            // MARKETING DIGITAL
            // ══════════════════════════════════════════════════════════════════
            [
                'cat'     => 'Marketing Digital',
                'title'   => 'SEO en 2025 : les signaux qui comptent vraiment pour Google',
                'short'   => 'Les algorithmes de Google évoluent constamment. Voici les facteurs de référencement qui ont réellement un impact aujourd\'hui, confirmés par les données terrain.',
                'image'   => 'https://images.unsplash.com/photo-1432888622747-4eb9a8efeb07?w=1200&auto=format&fit=crop',
                'content' => "Le référencement naturel en 2025 n'est plus une question de densité de mots-clés. Google est devenu suffisamment sophistiqué pour évaluer la qualité réelle d'une page.\n\n## Les Core Web Vitals\n\nTrois métriques sont particulièrement suivies : LCP (temps d'affichage du plus grand élément, objectif < 2,5s), INP (délai de réponse aux interactions, objectif < 200ms), CLS (stabilité visuelle, objectif < 0,1).\n\n## Le contenu E-E-A-T\n\nGoogle évalue désormais l'expérience firsthand de l'auteur. Un témoignage d'expert qui a vécu le processus pèse plus qu'un résumé générique. Cela signifie signer les articles avec des auteurs identifiables et inclure des données originales.\n\n## La recherche sémantique\n\nL'optimisation par mots-clés exacts est secondaire par rapport à la couverture sémantique d'un sujet. Un bon article couvre un thème en profondeur et répond aux questions connexes.\n\n## Ce qui ne marche plus\n\nLe bourrage de mots-clés, les liens achetés en masse, le contenu généré sans valeur ajoutée.\n\n## Conclusion\n\nLe SEO en 2025 récompense ce qui aurait toujours dû être la priorité : du contenu utile, écrit par des experts, sur un site rapide.",
                'comments' => [
                    ['name' => 'Stéphanie Brun',    'email' => 'stephanie.b@agence-seo.fr',  'msg' => "L'INP qui remplace le FID est encore sous-estimé par beaucoup d'agences. Bon article de synthèse."],
                    ['name' => 'Pierre-Yves Gautier','email' => 'pygautier@gmail.com',        'msg' => "La section E-E-A-T est la plus importante. Google distingue de mieux en mieux le contenu expert du contenu générique."],
                    ['name' => 'Nadia Lefèvre',      'email' => 'nlefevre@consulting.com',    'msg' => "Très bien écrit. Beaucoup de sites ne sont pas encore optimisés pour l'INP et vont perdre des positions."],
                ],
            ],
            [
                'cat'     => 'Marketing Digital',
                'title'   => 'Construire une stratégie de contenu qui génère des leads qualifiés',
                'short'   => 'Publier sans stratégie génère du trafic mais aucun client. Voici comment aligner chaque article avec un objectif business précis et mesurer ce qui compte.',
                'image'   => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=1200&auto=format&fit=crop',
                'content' => "La plupart des blogs d'entreprise échouent pour la même raison : ils publient sans stratégie, espérant que la quantité compensera l'absence de direction.\n\n## Partir des objectifs business\n\nAvant de décider des sujets, posez-vous : quels problèmes mes prospects cherchent-ils à résoudre avant d'acheter mon produit ? Cette question déplace le focus vers ce qui intéresse vos futurs clients.\n\n## Le tunnel TOFU / MOFU / BOFU\n\nTop of Funnel : contenu de sensibilisation pour attirer une audience large. Middle of Funnel : contenu d'évaluation pour les prospects qui comparent. Bottom of Funnel : contenu de décision avec études de cas.\n\n## Les métriques qui comptent\n\nOubliez le nombre de vues. Ce qui importe : le taux de conversion vers une inscription ou une démo, le temps sur la page, les articles qui génèrent des leads, et le revenu attribuable via UTM tracking.\n\n## La mise à jour du contenu existant\n\nMettre à jour un article qui performe est souvent plus rentable qu'en créer un nouveau. Google valorise la fraîcheur.\n\n## La fréquence idéale\n\nLa régularité prime sur la fréquence. Un article de fond par semaine construit une audience fidèle.",
                'comments' => [
                    ['name' => 'Benjamin Perrin', 'email' => 'b.perrin@startup-lyon.fr', 'msg' => "On a appliqué le modèle TOFU/MOFU/BOFU et les leads qualifiés ont augmenté de 40% en 6 mois. Le contenu BOFU convertit beaucoup mieux qu'on ne le pensait."],
                ],
            ],
            [
                'cat'     => 'Marketing Digital',
                'title'   => 'Email marketing : les techniques qui convertissent en 2025',
                'short'   => 'L\'email reste le canal avec le meilleur ROI du marketing digital. Mais les pratiques ont évolué. Voici ce qui fonctionne aujourd\'hui pour construire et monétiser une liste.',
                'image'   => 'https://images.unsplash.com/photo-1563986768494-4dee2763ff3f?w=1200&auto=format&fit=crop',
                'content' => "L'email marketing génère en moyenne 42€ pour chaque euro investi. C'est le canal avec le meilleur ROI du marketing digital, loin devant les réseaux sociaux.\n\n## La segmentation : le facteur clé\n\nEnvoyer le même email à toute votre liste est la principale erreur. La segmentation par comportement (pages visitées, emails ouverts, achats précédents) permet d'envoyer le bon message au bon moment.\n\n## L'automation intelligente\n\nLes séquences d'onboarding, de réengagement et de nurturing font le travail à votre place. Un nouvel abonné doit recevoir une séquence de bienvenue qui établit la confiance avant de proposer quoi que ce soit.\n\n## L'objet : la bataille de l'attention\n\nVous avez 3 secondes pour convaincre quelqu'un d'ouvrir votre email. Testez systématiquement vos objets avec des A/B tests. La personnalisation, la curiosité et l'urgence sont les leviers les plus efficaces.\n\n## La délivrabilité avant tout\n\nUn email non délivré ne convertit pas. Nettoyez régulièrement votre liste, authentifiez votre domaine (SPF, DKIM, DMARC) et évitez les mots spammeurs.\n\n## Mesurer ce qui compte\n\nLe taux d'ouverture est vanity metric depuis iOS 15. Concentrez-vous sur le taux de clic, le taux de conversion et le revenu par email.",
                'comments' => [
                    ['name' => 'Clara Fontaine', 'email' => 'clara.f@agence.fr', 'msg' => "La partie sur la délivrabilité est cruciale. On a doublé nos taux d'ouverture juste en nettoyant la liste et en configurant DMARC."],
                    ['name' => 'Marc Dupuis',    'email' => 'marc.d@ecommerce.fr', 'msg' => "L'automation de réengagement a réactivé 15% de nos abonnés inactifs. Ça vaut vraiment l'investissement."],
                ],
            ],


            // ══════════════════════════════════════════════════════════════════
            // DESIGN & UX
            // ══════════════════════════════════════════════════════════════════
            [
                'cat'     => 'Design & UX',
                'title'   => 'Design system : pourquoi en créer un et comment bien démarrer',
                'short'   => 'Un design system n\'est pas réservé aux grandes entreprises. Même pour une équipe de deux personnes, il accélère la conception et garantit la cohérence visuelle.',
                'image'   => 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=1200&auto=format&fit=crop',
                'content' => "Le terme « design system » intimide souvent les petites équipes. C'est une erreur. Un design system minimal change profondément la façon dont produit et développement collaborent.\n\n## Qu'est-ce qu'un design system ?\n\nC'est un ensemble de décisions de design documentées : couleurs, typographie, espacement, composants. Ce n'est pas un Figma bien rangé — c'est la combinaison d'une bibliothèque de composants et d'une documentation vivante.\n\n## Pourquoi en créer un\n\nPour les designers : moins de temps à recréer des composants. Pour les développeurs : une correspondance directe entre Figma et le code. Pour le produit : une cohérence visuelle qui renforce la confiance.\n\n## Par où commencer\n\nCommencez par auditer ce qui existe : recensez les couleurs en production (il y en a probablement 30% de trop), listez les tailles de texte, identifiez les composants récurrents.\n\n## Les tokens de design\n\nLes tokens sont le socle : des variables nommées qui représentent vos décisions visuelles. Ils créent le pont entre Figma et le code.\n\n## L'erreur à éviter\n\nVouloir tout documenter avant de livrer quoi que ce soit. Un design system se construit dans la continuité du produit.",
                'comments' => [
                    ['name' => 'Chloé Vidal',   'email' => 'chloe.v@designer-ux.fr', 'msg' => "L'audit comme point de départ est excellent. On a identifié 47 couleurs différentes dans notre app. Édifiant."],
                    ['name' => 'Florian Dupuy', 'email' => 'florian.d@gmail.com',    'msg' => "Les design tokens ont accéléré nos itérations. Le pont Figma-code est enfin solide."],
                ],
            ],
            [
                'cat'     => 'Design & UX',
                'title'   => 'L\'accessibilité web en pratique : au-delà des bonnes intentions',
                'short'   => 'L\'accessibilité n\'est pas une case à cocher. C\'est une discipline qui s\'intègre à chaque étape du design. Voici comment passer des principes WCAG à la pratique.',
                'image'   => 'https://images.unsplash.com/photo-1573164713988-8665fc963095?w=1200&auto=format&fit=crop',
                'content' => "En France, 12 millions de personnes vivent avec un handicap. Concevoir pour l'accessibilité n'est pas optionnel, c'est concevoir pour tout le monde.\n\n## Les quatre principes WCAG\n\nPerceptible : information présentée par tous les sens (alt text, sous-titres, contraste). Utilisable : tout fonctionne sans souris (navigation clavier, zones de clic 44x44px minimum). Compréhensible : langage clair, erreurs explicites. Robuste : code interprétable par les technologies d'assistance.\n\n## Les erreurs les plus courantes\n\nBoutons sans texte (l'icône seule n'est pas accessible, ajoutez un aria-label). Contraste insuffisant (ratio minimum 4,5:1 pour le texte). Focus clavier invisible (beaucoup de designs suppriment l'outline sans le remplacer).\n\n## Intégrer l'accessibilité dès le design\n\nL'accessibilité ajoutée après coup coûte 10 fois plus cher. Testez les contrastes dans Figma, annotez les niveaux de titres et les rôles ARIA, incluez la navigation clavier dans les tests utilisateurs.\n\n## Ressources\n\nLes notices AccesDénum du gouvernement français sont la référence francophone la plus complète pour appliquer le RGAA.",
                'comments' => [
                    ['name' => 'Lucie Hamelin',    'email' => 'lucie.h@accessibilite.org', 'msg' => "Merci de parler d'accessibilité de façon concrète. La mention du RGAA est importante pour les services publics français."],
                    ['name' => 'Sébastien Collet', 'email' => 'seb.c@dev-inclusif.fr',     'msg' => "Le focus visible est l'erreur la plus courante. J'aurais ajouté la gestion du focus sur les messages d'erreur pour les lecteurs d'écran."],
                ],
            ],
            [
                'cat'     => 'Design & UX',
                'title'   => 'UX Writing : comment les mots transforment l\'expérience utilisateur',
                'short'   => 'Les mots que vous choisissez dans votre interface ont autant d\'impact que la mise en page. L\'UX Writing est la discipline qui optimise le langage pour guider et rassurer les utilisateurs.',
                'image'   => 'https://images.unsplash.com/photo-1455390582262-044cdead277a?w=1200&auto=format&fit=crop',
                'content' => "Un bouton qui dit « Commencer » convertit mieux qu'un bouton « Soumettre ». C'est l'essence de l'UX Writing : choisir les mots qui servent l'utilisateur, pas l'entreprise.\n\n## Les principes fondamentaux\n\nClaire : l'utilisateur comprend immédiatement ce que fait un bouton ou ce que demande un formulaire. Concise : moins de mots, plus d'impact. Utile : chaque mot a une raison d'être. Humaine : parlons à nos utilisateurs comme à des personnes, pas à des machines.\n\n## Les messages d'erreur : l'occasion manquée\n\nLa plupart des messages d'erreur sont rédigés pour les développeurs. « Erreur 422 : champ invalide » n'aide personne. Rédigez des erreurs qui expliquent ce qui s'est passé ET comment le corriger.\n\n## Les micro-copies qui changent tout\n\nLe texte de placeholder, les tooltips, les états vides, les confirmations de succès — ces petites copies sont souvent bâclées. Elles constituent pourtant des moments clés de l'expérience.\n\n## Comment progresser\n\nAuditez vos copies actuelles avec le test de Hemingway : si une phrase est difficile à lire à voix haute, elle est trop complexe. Raccourcissez, simplifiez, humanisez.",
                'comments' => [
                    ['name' => 'Emma Richard', 'email' => 'emma.r@ux.fr', 'msg' => "Les messages d'erreur sont tellement importants. On a réduit les abandons de formulaire de 30% juste en améliorant les copies d'erreur."],
                ],
            ],

            // ══════════════════════════════════════════════════════════════════
            // ENTREPRENEURIAT
            // ══════════════════════════════════════════════════════════════════
            [
                'cat'     => 'Entrepreneuriat',
                'title'   => 'Lancer sa première startup en France : les étapes que personne ne dit',
                'short'   => 'Créer une startup est passionnant. Mais entre les statuts juridiques, le financement et la recherche des premiers clients, les obstacles sont nombreux. Retour sans filtre.',
                'image'   => 'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=1200&auto=format&fit=crop',
                'content' => "J'ai lancé ma première startup à 28 ans avec 15 000 euros d'économies. Trois ans plus tard, j'ai appris plus sur le business en faisant que pendant toutes mes années d'études.\n\n## Choisir le bon statut juridique\n\nLa SASU est le statut de référence pour les startups françaises qui souhaitent lever des fonds. La micro-entreprise est idéale pour valider une idée sans frais fixes, mais devient vite contraignante.\n\n## Valider avant de coder\n\nL'erreur classique : passer 6 mois à construire un produit avant de parler à un seul client. Faites 20 entretiens problème pour confirmer que le problème est réel, créez une landing page pour mesurer l'intérêt réel.\n\n## Le financement en France\n\nBPI France propose des prêts d'amorçage et des garanties bancaires. Le label French Tech offre visibilité et accès aux programmes. Les Business Angels investissent en early stage avec du mentorat.\n\n## L'équipe est tout\n\nAucun investisseur sérieux ne finance une idée. Ils financent une équipe. Trouver un co-fondateur complémentaire multiplie les chances de succès.\n\n## Le conseil le plus important\n\nCommencez à facturer dès que possible. Le premier euro d'un vrai client apprend plus que n'importe quelle étude ou accélérateur.",
                'comments' => [
                    ['name' => 'Pauline Chevalier',  'email' => 'pauline.c@entrepreneur.fr',  'msg' => "Le conseil sur la validation avant de coder est celui que j'aurais aimé recevoir il y a 3 ans. On a passé 8 mois à construire quelque chose que personne ne voulait acheter."],
                    ['name' => 'Marc-Antoine Faure', 'email' => 'mafaure@bpifrance-alumni.fr', 'msg' => "Les concours i-Nov et i-Lab de BPI sont une source de financement non-dilutif souvent ignorée."],
                ],
            ],
            [
                'cat'     => 'Entrepreneuriat',
                'title'   => 'Freelance : fixer son TJM sans se sous-estimer',
                'short'   => 'Beaucoup de freelances se sous-évaluent par peur de perdre des clients. Voici comment calculer un TJM qui couvre vos charges, vos objectifs et votre valeur réelle.',
                'image'   => 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=1200&auto=format&fit=crop',
                'content' => "Le TJM (Taux Journalier Moyen) est la première décision financière d'un freelance. Et c'est souvent la plus mal prise, par manque de méthode.\n\n## Le calcul de base\n\nCommencez par vos charges annuelles : cotisations sociales, impôts, assurances, logiciels, formation. Ajoutez votre objectif de revenu net. Divisez par le nombre de jours facturables réels (environ 200 jours sur 220 ouvrés, une fois les congés et la prospection déduits).\n\n## Les coûts invisibles\n\nLe freelance facture en moyenne 70% de son temps de travail. Le reste part en administratif, prospection, veille et formation. Ces heures ont un coût qui doit être intégré dans votre TJM.\n\n## La valeur vs le temps\n\nUn TJM n'est pas qu'un coût horaire — c'est le reflet de votre valeur. Un développeur senior qui règle un problème complexe en 2 heures apporte la même valeur qu'un junior qui passerait 2 jours. Facturez la valeur, pas le temps.\n\n## Comment augmenter son TJM\n\nSpécialisez-vous sur une niche, construisez votre réputation en ligne, demandez des recommandations, et augmentez progressivement à chaque renouvellement de contrat.",
                'comments' => [
                    ['name' => 'Eléonore Simonin', 'email' => 'eleonore.s@freelance.fr', 'msg' => "La distinction valeur vs temps est la plus importante. J'ai augmenté mon TJM de 30% en me spécialisant sur une niche et en le justifiant par les résultats."],
                ],
            ],
            [
                'cat'     => 'Entrepreneuriat',
                'title'   => 'Lever des fonds en France : guide pour les fondateurs débutants',
                'short'   => 'La levée de fonds est souvent mythifiée. Voici comment préparer votre pitch, choisir les bons investisseurs et éviter les pièges des term sheets.',
                'image'   => 'https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?w=1200&auto=format&fit=crop',
                'content' => "La levée de fonds n'est pas une fin en soi — c'est un outil de croissance qui a un coût : la dilution. Avant de lever, assurez-vous que c'est vraiment ce dont votre projet a besoin.\n\n## Les types de financement\n\nLe bootstrapping (autofinancement) vous garde maître à bord mais limite la vitesse. Les Business Angels apportent du capital early stage et souvent du mentorat. Les VC (Venture Capital) investissent dans les startups à fort potentiel de croissance. BPI France propose des prêts non-dilutifs.\n\n## Préparer son pitch deck\n\nUn pitch deck efficace couvre en 10 slides : le problème, la solution, le marché, le modèle économique, la traction, l'équipe, la concurrence, la roadmap et le montant demandé.\n\n## La due diligence\n\nLes investisseurs vérifieront tout : vos chiffres, votre cap table, vos contrats, votre IP. Ayez une data room propre et à jour.\n\n## Les pièges des term sheets\n\nLes clauses de liquidation préférentielle, de ratchet et de drag-along peuvent avoir des conséquences importantes. Faites-vous accompagner par un avocat spécialisé avant de signer.\n\n## La règle des 18 mois\n\nNe levez que si vous avez 18 mois de runway après clôture. Lever trop tard vous met en position de faiblesse.",
                'comments' => [
                    ['name' => 'Vincent Barbier', 'email' => 'v.barbier@vc-france.fr', 'msg' => "La section sur les term sheets est sous-estimée. Trop de fondateurs signent sans comprendre les clauses de liquidation."],
                    ['name' => 'Isabelle Morin',  'email' => 'imorin@bpifrance.fr',    'msg' => "Bon article. J'ajouterais le dispositif JEI (Jeune Entreprise Innovante) qui offre des exonérations fiscales et sociales importantes."],
                ],
            ],


            // ══════════════════════════════════════════════════════════════════
            // INTELLIGENCE ARTIFICIELLE
            // ══════════════════════════════════════════════════════════════════
            [
                'cat'     => 'Intelligence Artificielle',
                'title'   => 'Intégrer l\'IA dans votre workflow de développement : guide pratique',
                'short'   => 'Les outils d\'IA amplifient la productivité des développeurs. Voici comment intégrer concrètement GitHub Copilot, Claude et les LLM dans votre quotidien technique.',
                'image'   => 'https://images.unsplash.com/photo-1677442135703-1787eea5ce01?w=1200&auto=format&fit=crop',
                'content' => "En 2025, un développeur qui n'utilise pas d'outils IA est moins efficace que ses pairs. La question n'est plus « faut-il adopter l'IA ? » mais « comment l'adopter intelligemment ? ».\n\n## Les cas d'usage où l'IA excelle\n\nGénération de code boilerplate : tests unitaires, migrations, fichiers de configuration. Compréhension de code inconnu : coller un fichier et demander une explication. Refactoring : demander de réécrire une fonction en respectant un principe donné. Documentation : générer la JSDoc d'une fonction existante.\n\n## Les cas d'usage où l'IA échoue\n\nL'IA hallucine sur des APIs récentes. Elle génère du code fonctionnel en apparence mais avec des failles de sécurité. Elle reproduit des patterns courants même quand votre architecture est différente.\n\nLa règle : ne jamais copier-coller du code généré sans le comprendre.\n\n## Construire un contexte efficace\n\nLa qualité d'une réponse IA est proportionnelle à la qualité du prompt. Fournissez le langage et la version, les conventions du projet, les contraintes techniques, un exemple de code existant.\n\n## Les RAG pour les assistants métier\n\nLes RAG (Retrieval-Augmented Generation) connectent un LLM à votre documentation interne. Le résultat : un assistant qui répond en tenant compte de votre contexte spécifique.",
                'comments' => [
                    ['name' => 'Thibault Grimaud', 'email' => 'thibault.g@tech.io',     'msg' => "La mise en garde sur le copier-coller est essentielle. J'ai vu des failles de sécurité introduites par des juniors qui faisaient confiance aveuglément au code généré."],
                    ['name' => 'Mélanie Jacquet',  'email' => 'mjacquet@datanomad.fr',  'msg' => "On a mis en place un RAG sur notre documentation interne. Le gain de temps pour l'onboarding des nouveaux développeurs est spectaculaire."],
                ],
            ],
            [
                'cat'     => 'Intelligence Artificielle',
                'title'   => 'Comprendre les LLM sans être data scientist',
                'short'   => 'Vous utilisez ChatGPT ou Claude chaque jour, mais comprenez-vous comment ils fonctionnent ? Ce guide explique les concepts clés de façon accessible et sans mathématiques.',
                'image'   => 'https://images.unsplash.com/photo-1676277791608-ac54525aa94d?w=1200&auto=format&fit=crop',
                'content' => "Les grands modèles de langage sont devenus des outils du quotidien. Comprendre les concepts fondamentaux aide à les utiliser mieux et à éviter les erreurs courantes.\n\n## Ce qu'est vraiment un LLM\n\nUn LLM est un modèle statistique entraîné à prédire le prochain token dans une séquence de texte. L'entraînement se fait sur des quantités astronomiques de texte, ce qui permet au modèle de compresser une représentation statistique du langage humain.\n\n## La fenêtre de contexte\n\nLa fenêtre de contexte est la quantité de texte qu'un modèle peut « voir » à un instant donné. Le modèle ne mémorise rien entre les conversations. Chaque appel est indépendant.\n\n## Les hallucinations\n\nUn LLM génère une réponse probable basée sur ses paramètres. Quand il ne sait pas, il génère quelque chose de plausible. Les hallucinations sont plus fréquentes sur les données récentes, les noms propres et les chiffres précis.\n\n## Temperature et sampling\n\nLe paramètre temperature contrôle l'aléatoire. Temperature basse pour du code (déterministe), température haute pour la créativité.\n\n## Fine-tuning vs prompting\n\nPour 95% des besoins, un bon prompting et une architecture RAG donnent des résultats équivalents au fine-tuning, sans les coûts.",
                'comments' => [
                    ['name' => 'Arthur Leconte', 'email' => 'arthur.l@ia-accessible.fr', 'msg' => "Excellente vulgarisation. La distinction fine-tuning vs RAG est souvent mal comprise et votre explication est la plus claire que j'ai lue en français."],
                ],
            ],
            [
                'cat'     => 'Intelligence Artificielle',
                'title'   => 'Prompt engineering : les techniques avancées pour de meilleurs résultats',
                'short'   => 'La qualité d\'un prompt détermine la qualité de la réponse. Découvrez les techniques de chain-of-thought, few-shot learning et les patterns avancés qui transforment vos résultats.',
                'image'   => 'https://images.unsplash.com/photo-1698847137740-b4d9ff0cee57?w=1200&auto=format&fit=crop',
                'content' => "Le prompt engineering n'est pas une science mystérieuse. C'est l'art de communiquer clairement avec un modèle de langage pour obtenir exactement ce dont vous avez besoin.\n\n## Les fondamentaux\n\nSoyez spécifique sur le rôle : « Tu es un expert en cybersécurité spécialisé en pentest web » donne de meilleurs résultats qu'un prompt sans contexte. Donnez le format attendu : tableau, liste numérotée, code, JSON.\n\n## Chain of Thought (CoT)\n\nDemander au modèle de « réfléchir étape par étape » améliore significativement les performances sur les problèmes complexes. Le modèle externalise son raisonnement et fait moins d'erreurs.\n\n## Few-Shot Learning\n\nDonner des exemples de ce que vous attendez (input → output) améliore la cohérence des réponses. 2 à 5 exemples suffisent généralement.\n\n## System prompts\n\nDans les API, le system prompt définit le comportement global du modèle pour toute la conversation. C'est là que vous définissez le persona, les contraintes et le format de sortie.\n\n## Itérer et tester\n\nTreatez vos prompts comme du code : versionnez-les, testez-les sur des cas représentatifs, mesurez les résultats.",
                'comments' => [
                    ['name' => 'Sophie Laurent', 'email' => 'sophie.l@ia-studio.fr', 'msg' => "Le Chain of Thought est sous-utilisé. Sur des problèmes de raisonnement complexe, ça change vraiment la qualité des réponses."],
                    ['name' => 'David Noir',     'email' => 'david.n@tech-conseil.fr', 'msg' => "L'approche few-shot est particulièrement efficace pour les tâches de classification ou de génération formatée. Très bon article."],
                ],
            ],

            // ══════════════════════════════════════════════════════════════════
            // CYBERSÉCURITÉ
            // ══════════════════════════════════════════════════════════════════
            [
                'cat'     => 'Cybersécurité',
                'title'   => 'Les vulnérabilités web les plus exploitées en 2025',
                'short'   => 'L\'OWASP Top 10 reste la référence pour sécuriser les applications web. Voici les vulnérabilités actuellement les plus ciblées et les contre-mesures concrètes à mettre en place.',
                'image'   => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=1200&auto=format&fit=crop',
                'content' => "La sécurité d'une application web n'est pas un état qu'on atteint, c'est une discipline qu'on pratique en continu.\n\n## Injection SQL\n\nToujours présente malgré des décennies de sensibilisation. La protection est simple : n'utilisez jamais de requêtes SQL construites par concaténation. Utilisez systématiquement des requêtes préparées ou un ORM.\n\n## Cross-Site Scripting (XSS)\n\nL'injection de JavaScript dans des pages web consultées par d'autres utilisateurs. Se protège par l'échappement systématique des données affichées et une Content Security Policy bien configurée.\n\n## Broken Authentication\n\nMots de passe faibles, tokens non expirés, absence de rate limiting. Utilisez bcrypt ou argon2, invalidez les tokens lors de la déconnexion, limitez les tentatives.\n\n## IDOR (Insecure Direct Object References)\n\nUn utilisateur peut accéder aux ressources d'un autre en changeant un ID dans l'URL. Vérifiez systématiquement que l'utilisateur authentifié est propriétaire de la ressource.\n\n## La règle des moindres privilèges\n\nChaque composant ne doit avoir accès qu'à ce dont il a strictement besoin. Un utilisateur de BDD dédié ne devrait jamais avoir les droits DROP en production.",
                'comments' => [
                    ['name' => 'Vincent Barbier', 'email' => 'v.barbier@cybersec.fr',  'msg' => "L'IDOR est la vulnérabilité la plus fréquente dans les audits que je réalise. Les développeurs pensent que l'authentification suffit, sans vérifier l'autorisation."],
                    ['name' => 'Isabelle Morin',  'email' => 'imorin@ssi-conseil.fr',  'msg' => "J'ajouterais les en-têtes de sécurité HTTP : HSTS, X-Frame-Options, X-Content-Type-Options. Configurables en 10 minutes et souvent oubliés."],
                ],
            ],
            [
                'cat'     => 'Cybersécurité',
                'title'   => 'Sécuriser son infrastructure cloud : les bonnes pratiques AWS et GCP',
                'short'   => 'Le cloud ne signifie pas sécurité automatique. Voici les configurations critiques à mettre en place dès le premier jour pour éviter les incidents les plus courants.',
                'image'   => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=1200&auto=format&fit=crop',
                'content' => "La majorité des incidents cloud sont dus à des erreurs de configuration, pas à des vulnérabilités techniques. Le modèle de responsabilité partagée signifie que vous êtes responsable de sécuriser ce que vous déployez.\n\n## Identity and Access Management (IAM)\n\nPrincipe des moindres privilèges : chaque service, utilisateur et application ne doit avoir que les permissions strictement nécessaires. N'utilisez jamais les credentials root. Activez le MFA sur tous les comptes d'administration.\n\n## Les buckets S3 / Storage publics\n\nNe rendez jamais un bucket public par défaut. Utilisez les politiques de bucket pour contrôler précisément qui peut accéder à quoi. Activez la journalisation des accès.\n\n## Les groupes de sécurité réseau\n\nN'ouvrez jamais le port 22 (SSH) ou 3389 (RDP) sur 0.0.0.0/0. Utilisez un VPN ou un bastion host pour les accès d'administration.\n\n## La gestion des secrets\n\nNe stockez jamais de clés API, de mots de passe ou de certificats dans le code source ou les variables d'environnement en clair. Utilisez AWS Secrets Manager, GCP Secret Manager ou HashiCorp Vault.\n\n## La surveillance et les alertes\n\nActivez CloudTrail (AWS) ou Cloud Audit Logs (GCP) pour journaliser toutes les actions. Configurez des alertes sur les activités suspectes.",
                'comments' => [
                    ['name' => 'Thomas Berger', 'email' => 'thomas.b@cloud-sec.fr', 'msg' => "La gestion des secrets est le point le plus critique. On voit encore des clés AWS en clair dans des repos GitHub publics en 2025. C'est alarmant."],
                ],
            ],
            [
                'cat'     => 'Cybersécurité',
                'title'   => 'Authentification et autorisation : ne confondez plus les deux',
                'short'   => 'L\'authentification vérifie qui vous êtes. L\'autorisation détermine ce que vous pouvez faire. Cette distinction fondamentale est à la source de nombreuses failles de sécurité.',
                'image'   => 'https://images.unsplash.com/photo-1614064641938-3bbee52942c7?w=1200&auto=format&fit=crop',
                'content' => "Authentication vs Authorization : deux concepts souvent confondus, deux mécanismes distincts qui doivent être implémentés séparément.\n\n## Authentification : qui êtes-vous ?\n\nL'authentification vérifie l'identité d'un utilisateur. Les mécanismes courants : mot de passe (facteur de connaissance), OTP/TOTP (facteur de possession), biométrie (facteur d'inhérence). L'authentification multi-facteurs combine plusieurs facteurs.\n\n## Autorisation : que pouvez-vous faire ?\n\nL'autorisation détermine les actions permises après authentification. Les modèles courants : RBAC (Role-Based Access Control), ABAC (Attribute-Based), ACL (Access Control List).\n\n## Les erreurs classiques\n\nVérifier seulement si l'utilisateur est connecté (authentification) sans vérifier s'il a le droit d'accéder à la ressource spécifique (autorisation). C'est l'IDOR : je suis connecté en tant qu'utilisateur A, mais j'accède aux données de l'utilisateur B en changeant l'ID dans l'URL.\n\n## JWT : avantages et pièges\n\nLes JSON Web Tokens sont sans état et scalables. Mais leurs failles sont nombreuses si mal configurés : algorithme « none », clé secrète faible, absence de validation de l'expiration.\n\n## OAuth 2.0 et OpenID Connect\n\nOAuth 2.0 est un framework d'autorisation (délégation de permissions). OpenID Connect est une couche d'authentification au-dessus. Utilisez des bibliothèques éprouvées, n'implémentez jamais OAuth from scratch.",
                'comments' => [
                    ['name' => 'Lucie Bernard', 'email' => 'lucie.b@securite.fr', 'msg' => "La confusion Auth/Authz est à l'origine de tant de vulnérabilités. Merci pour cet article clair et pédagogique."],
                    ['name' => 'Marc Petit',    'email' => 'marc.p@pentest.fr',   'msg' => "La partie JWT est très pertinente. L'algorithme 'none' est une vulnérabilité classic qu'on trouve encore régulièrement en audit."],
                ],
            ],


            // ══════════════════════════════════════════════════════════════════
            // PRODUCTIVITÉ
            // ══════════════════════════════════════════════════════════════════
            [
                'cat'     => 'Productivité',
                'title'   => 'Les systèmes de productivité qui fonctionnent vraiment pour les développeurs',
                'short'   => 'Entre les tickets Jira, les PRs, les réunions et le deep work, les développeurs jonglent avec tout. Voici les systèmes concrets qui permettent de rester focus sans s\'épuiser.',
                'image'   => 'https://images.unsplash.com/photo-1484480974693-6ca0a78fb36b?w=1200&auto=format&fit=crop',
                'content' => "La productivité d'un développeur ne se mesure pas en lignes de code écrites. Elle se mesure en problèmes résolus et en valeur livrée. Voici les systèmes qui font la différence.\n\n## Le time blocking : protéger le deep work\n\nLes interruptions détruisent la productivité technique. Chaque interruption coûte en moyenne 23 minutes de récupération de contexte. Bloquez des plages de 2 à 4 heures sans réunion ni Slack pour le travail de fond.\n\n## La technique Pomodoro adaptée au développement\n\n25 minutes de focus, 5 minutes de pause. Après 4 Pomodoros, une pause longue de 15 à 30 minutes. Adaptez la durée selon la nature des tâches : les tâches de debug bénéficient de sessions plus longues.\n\n## Gérer les interruptions\n\nActivez le mode Ne pas déranger pendant les sessions de travail profond. Communiquez vos horaires de disponibilité à votre équipe. Regroupez les réponses aux messages en créneaux définis.\n\n## La règle des deux minutes\n\nSi une tâche prend moins de deux minutes, faites-la immédiatement. Sinon, planifiez-la. Ce simple filtre réduit considérablement l'accumulation de petites tâches.\n\n## Le weekly review du développeur\n\nChaque vendredi : passez en revue les PRs ouvertes, les bugs non résolus, la dette technique accumulée, et planifiez la semaine suivante. 30 minutes qui évitent les surprises du lundi matin.",
                'comments' => [
                    ['name' => 'Romain Lefort', 'email' => 'romain.l@dev-zen.fr',   'msg' => "Le time blocking a changé ma façon de travailler. J'ai bloqué mes matins pour le deep work et ma productivité a augmenté d'au moins 40%."],
                    ['name' => 'Clara Morel',   'email' => 'clara.m@tech-team.fr',  'msg' => "La règle des deux minutes est sous-estimée. Simple mais redoutablement efficace pour vider le backlog mental."],
                ],
            ],
            [
                'cat'     => 'Productivité',
                'title'   => 'Apprendre à apprendre : les méthodes validées par la science cognitive',
                'short'   => 'Dans un secteur qui évolue aussi vite que la tech, savoir apprendre efficacement est une compétence fondamentale. Voici les méthodes validées par la recherche pour optimiser votre apprentissage.',
                'image'   => 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=1200&auto=format&fit=crop',
                'content' => "La plupart des gens apprennent de façon inefficace. Ils relisent leurs notes, regardent des tutoriels passifs, et se sentent confiants car ils reconnaissent le contenu. Cette illusion de maîtrise est le principal obstacle à l'apprentissage réel.\n\n## Le retrieval practice (la récupération active)\n\nL'apprentissage est renforcé non pas quand vous encodez l'information (en lisant ou écoutant), mais quand vous la récupérez depuis la mémoire. Les flashcards, les quiz et la pratique délibérée sont plus efficaces que la relecture.\n\n## Le spaced repetition\n\nRévisez une information juste avant de l'oublier. Les intervalles croissants (1 jour, 3 jours, 1 semaine, 1 mois) maximisent la rétention à long terme. Des outils comme Anki automatisent ce processus.\n\n## L'interleaving\n\nAlterner entre plusieurs sujets ou problèmes (plutôt que bloquer sur un seul) améliore la capacité à discriminer et à appliquer les connaissances. Contre-intuitif mais validé par la recherche.\n\n## Le projet comme vecteur d'apprentissage\n\nApprendre par la pratique sur un vrai projet ancre les connaissances beaucoup plus efficacement que tout cours théorique. Choisissez un projet qui vous force à confronter vos lacunes.\n\n## La surcharge cognitive\n\nNe tentez pas d'apprendre trop de choses à la fois. La mémoire de travail est limitée à 4 à 7 éléments simultanés. Concentrez-vous sur un concept à la fois.",
                'comments' => [
                    ['name' => 'Arnaud Simon', 'email' => 'arnaud.s@apprendre.fr', 'msg' => "Anki a transformé ma façon d'apprendre les nouvelles technologies. Le spaced repetition est vraiment contre-intuitif mais incroyablement efficace."],
                ],
            ],
            [
                'cat'     => 'Productivité',
                'title'   => 'Réunions efficaces : comment récupérer votre temps de travail',
                'short'   => 'Les développeurs perdent en moyenne 15 heures par semaine en réunions inutiles. Voici comment transformer vos réunions en moments de décision réels et protéger votre temps de focus.',
                'image'   => 'https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=1200&auto=format&fit=crop',
                'content' => "La pire chose qui puisse arriver à un développeur en milieu de session de travail profond : une notification de réunion « urgente » dans 5 minutes. Les réunions sont souvent le principal ennemi de la productivité technique.\n\n## Le problème des réunions par défaut\n\nLa réunion d'une heure est la durée par défaut dans la plupart des calendriers. Mais la majorité des décisions peuvent être prises en 15 ou 30 minutes si la réunion est bien préparée.\n\n## Les règles d'une bonne réunion\n\nPas de réunion sans ordre du jour clair. Commencez à l'heure, terminez à l'heure. Décisions documentées en temps réel. Seules les personnes qui ont une raison d'être là sont invitées.\n\n## Les réunions qui peuvent être un email\n\nLes mises à jour de statut, les partages d'information unidirectionnels, les rapports réguliers — tout ce qui ne nécessite pas de décision ou de co-création peut être asynchrone.\n\n## La réunion de synchronisation\n\nLe daily standup de 15 minutes (qu'ai-je fait, que vais-je faire, y a-t-il des blocages) est le seul format de réunion récurrente généralement justifiée pour les équipes de développement.\n\n## Protéger votre temps de focus\n\nNégociez des plages sans réunion dans votre emploi du temps. Regroupez les réunions obligatoires sur des créneaux définis, de préférence en début ou fin de journée.",
                'comments' => [
                    ['name' => 'Julie Fontaine',  'email' => 'julie.f@agile-team.fr',  'msg' => "On a remplacé les réunions de status par un canal Slack asynchrone. On a récupéré 4 heures par semaine par développeur."],
                    ['name' => 'Pierre Dupont',   'email' => 'pierre.d@tech-mgr.fr',   'msg' => "La règle 'pas de réunion sans ordre du jour' a transformé la culture de notre équipe. Maintenant les gens préparent avant de demander du temps."],
                ],
            ],

        ]; // fin getArticlesData()
    }
}
