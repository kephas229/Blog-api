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
        // Idempotent : ne seed que si la base est vide
        if (User::count() > 0) {
            $this->command?->info('Base déjà peuplée, seed ignoré.');
            return;
        }

        // ─── 1. Compte administrateur principal ───────────────────────────────
        $admin = User::create([
            'name'     => 'Sophie Marchand',
            'email'    => 'admin@blogflow.fr',
            'password' => Hash::make('Admin@2024!'),
        ]);

        // ─── 2. Rédacteurs de la plateforme ───────────────────────────────────
        $authors = collect([
            ['name' => 'Thomas Leroy',   'email' => 'thomas.leroy@blogflow.fr',   'password' => Hash::make('Thomas@2024!')],
            ['name' => 'Camille Dubois', 'email' => 'camille.dubois@blogflow.fr', 'password' => Hash::make('Camille@2024!')],
            ['name' => 'Lucas Martin',   'email' => 'lucas.martin@blogflow.fr',   'password' => Hash::make('Lucas@2024!')],
            ['name' => 'Elodie Bernard', 'email' => 'elodie.bernard@blogflow.fr', 'password' => Hash::make('Elodie@2024!')],
        ])->map(fn($data) => User::create($data));

        $allAuthors = $authors->prepend($admin);

        // ─── 3. Catégories ────────────────────────────────────────────────────
        $categories = collect([
            'Développement Web',
            'Marketing Digital',
            'Design & UX',
            'Entrepreneuriat',
            'Intelligence Artificielle',
            'Cybersécurité',
        ])->mapWithKeys(fn($name) => [$name => Category::create(['name' => $name])]);

        // ─── 4. Articles ──────────────────────────────────────────────────────
        $this->seedArticles($allAuthors, $categories);

        // ─── 5. Commentaires ──────────────────────────────────────────────────
        $this->seedComments();
    }

    private function seedArticles($authors, $categories): void
    {
        foreach ($this->getArticlesData() as $data) {
            Article::create([
                'title'             => $data['title'],
                'short_description' => $data['short_description'],
                'content'           => $data['content'],
                'image'             => null,
                'user_id'           => $authors->random()->id,
                'category_id'       => $categories[$data['category']]->id,
            ]);
        }
    }

    private function seedComments(): void
    {
        $articles = Article::all();
        foreach ($this->getCommentsData() as $comment) {
            $article = isset($comment['article_index'])
                ? $articles->get($comment['article_index'])
                : $articles->random();
            if (!$article) continue;
            Comment::create([
                'visitor_name'  => $comment['name'],
                'visitor_email' => $comment['email'],
                'message'       => $comment['message'],
                'article_id'    => $article->id,
            ]);
        }
    }

    private function getArticlesData(): array
    {
        return [

            // ── Développement Web ─────────────────────────────────────────────
            [
                'title'             => 'Les fondamentaux de React 19 : ce qui change vraiment pour les développeurs',
                'short_description' => "React 19 introduit des changements profonds dans la gestion des états et des actions serveur. Découvrez ce que vous devez absolument maîtriser avant de migrer vos projets.",
                'category'          => 'Développement Web',
                'content'           => "React 19 est sans doute la mise à jour la plus significative de la bibliothèque depuis l'introduction des hooks. Elle redéfinit la façon dont nous pensons la gestion des états, des formulaires et des interactions côté serveur.\n\n## Les Actions : la fin des useState imbriqués\n\nJusqu'à présent, gérer un formulaire asynchrone nécessitait d'enchaîner useState pour le chargement, les erreurs et les données. React 19 introduit les Actions, qui permettent de centraliser tout ce flux dans une seule fonction asynchrone.\n\nLe hook useActionState prend en charge automatiquement l'état pending, les erreurs et la réponse — sans aucune orchestration manuelle.\n\n## Le compilateur React : moins de mémoïsation à écrire\n\nLe compilateur React (anciennement React Forget) est désormais intégré. Il analyse votre code à la compilation et applique automatiquement les optimisations que vous faisiez manuellement avec useMemo et useCallback.\n\nConcrètement, vous n'aurez plus à gérer manuellement les dépendances de mémoïsation. Le compilateur le détecte et l'optimise seul. Cela simplifie considérablement la base de code et réduit les bugs de dépendances manquantes.\n\n## use() : lire des promesses directement dans le rendu\n\nLa nouvelle fonction use() permet de lire la valeur d'une Promise ou d'un Context directement dans le corps d'un composant. Couplée à Suspense, elle offre une expérience de chargement beaucoup plus fluide et naturelle.\n\n## Conclusion\n\nReact 19 ne casse rien, mais il ouvre une nouvelle façon de construire des interfaces. Le mieux est de commencer par migrer un petit projet ou une fonctionnalité isolée pour prendre en main ces concepts avant de les déployer en production.",
            ],
            [
                'title'             => 'Construire une API REST robuste avec Laravel 11 : guide complet',
                'short_description' => "De la conception des routes à la sécurisation avec Sanctum, ce guide couvre tout ce qu'il faut savoir pour livrer une API Laravel prête pour la production.",
                'category'          => 'Développement Web',
                'content'           => "Laravel reste le framework PHP le plus populaire pour une bonne raison : il offre une expérience développeur exceptionnelle tout en étant suffisamment puissant pour les projets à grande échelle. Avec Laravel 11, plusieurs améliorations rendent la création d'API encore plus fluide.\n\n## La structure d'une API bien pensée\n\nAvant d'écrire la première ligne de code, la conception compte. Une bonne API REST respecte quelques principes fondamentaux :\n\n- Les ressources sont des noms, pas des verbes : /articles et non /getArticles\n- Les verbes HTTP portent l'action : GET pour lire, POST pour créer, PUT pour modifier, DELETE pour supprimer\n- Les codes HTTP sont sémantiques : 201 pour une création réussie, 422 pour une validation échouée, 404 pour une ressource introuvable\n\n## Form Requests : la validation propre\n\nDéléguer la validation à des Form Requests est une décision d'architecture qui paie à long terme. Le controller reste lisible, et les règles de validation sont testables de façon indépendante.\n\n## Sécurisation avec Sanctum\n\nPour une API consommée par un SPA, Sanctum est le choix idéal. Il émet des tokens personnels stockés en base de données, révocables à la demande, et s'intègre naturellement au système d'authentification de Laravel.\n\n## Les Resources : contrôler la réponse JSON\n\nLes API Resources permettent de définir précisément ce que l'API expose. C'est la couche de transformation entre votre modèle Eloquent et la réponse JSON envoyée au client. Cela protège contre l'exposition accidentelle de champs sensibles.\n\n## Conseil final\n\nDocumentez votre API dès le début avec L5-Swagger ou Scribe. Une API sans documentation est une API difficile à maintenir et à consommer par votre équipe front-end.",
            ],
            [
                'title'             => 'Tailwind CSS v4 : tout ce qui change dans la nouvelle version',
                'short_description' => "Tailwind v4 abandonne le fichier de configuration JavaScript au profit d'une configuration CSS native. Voici ce que cela implique concrètement pour vos projets.",
                'category'          => 'Développement Web',
                'content'           => "Tailwind CSS v4 marque un tournant dans la façon dont le framework est configuré et utilisé. La philosophie reste la même — des classes utilitaires composables — mais l'infrastructure sous-jacente a été entièrement repensée.\n\n## Fin du tailwind.config.js\n\nLa configuration se fait désormais directement dans votre fichier CSS principal, via des variables et des directives CSS natives. C'est un changement majeur qui simplifie l'intégration dans des projets qui ne sont pas 100% JavaScript. Les tokens de design sont désormais de vraies variables CSS, utilisables n'importe où dans votre feuille de style.\n\n## Un moteur de compilation réécrit\n\nTailwind v4 utilise un nouveau moteur basé sur Rust (via LightningCSS) qui est environ 5 fois plus rapide que l'ancien. Sur de gros projets, cela se traduit par des démarrages de serveur de développement quasi-instantanés.\n\n## Les variants dynamiques\n\nIl est maintenant possible de créer des variants personnalisés à la volée directement dans les classes HTML, ce qui offre une flexibilité inédite sans sortir du framework.\n\n## Migration depuis v3\n\nLa migration n'est pas automatique, mais Tailwind fournit un outil de codemod qui transforme la majorité du code existant. Les classes qui ont changé de nom sont peu nombreuses, et le guide de migration est clair et bien documenté.\n\n## Mon avis\n\nv4 est une évolution naturelle et bienvenue. La configuration CSS-first est plus intuitive, et le gain de performance est réel. Je recommande de migrer les nouveaux projets immédiatement et de planifier la migration des projets existants après la sortie de la version stable.",
            ],


            // ── Marketing Digital ─────────────────────────────────────────────
            [
                'title'             => "SEO en 2025 : les signaux qui comptent vraiment pour Google",
                'short_description' => "Les algorithmes de Google évoluent constamment. Voici les facteurs de référencement qui ont réellement un impact aujourd'hui, confirmés par les données et les tests terrain.",
                'category'          => 'Marketing Digital',
                'content'           => "Le référencement naturel en 2025 n'est plus une question de densité de mots-clés ou de backlinks en masse. Google est devenu suffisamment sophistiqué pour évaluer la qualité réelle d'une page et l'expérience qu'elle offre aux utilisateurs.\n\n## L'expérience utilisateur comme signal de classement\n\nLes Core Web Vitals sont désormais des facteurs de classement confirmés. Trois métriques sont particulièrement suivies :\n\n- LCP (Largest Contentful Paint) : le temps d'affichage du plus grand élément visible. Objectif : sous 2,5 secondes.\n- INP (Interaction to Next Paint) : le délai de réponse aux interactions utilisateur. Objectif : sous 200 ms.\n- CLS (Cumulative Layout Shift) : la stabilité visuelle de la page. Objectif : score inférieur à 0,1.\n\nUn site lent ou instable perd des positions, peu importe la qualité de son contenu.\n\n## Le contenu E-E-A-T : Expérience, Expertise, Autorité, Confiance\n\nGoogle évalue désormais l'expérience firsthand de l'auteur. Pour un article sur la création d'entreprise, un témoignage d'entrepreneur qui a vécu le processus pèse plus qu'un résumé générique.\n\nConcrètement, cela signifie signer les articles avec des auteurs identifiables et crédibles, inclure des données originales et des exemples concrets, et lier à des sources faisant autorité dans le domaine.\n\n## La recherche sémantique et les entités\n\nL'optimisation par mots-clés exacts est secondaire par rapport à la couverture sémantique d'un sujet. Un bon article couvre un thème en profondeur, répond aux questions connexes, et utilise le vocabulaire naturel du domaine.\n\nLes cocons sémantiques (topic clusters) restent une stratégie efficace : une page pilier couvre un sujet en largeur, des pages satellites l'approfondissent sur des angles spécifiques.\n\n## Ce qui ne marche plus\n\nLe bourrage de mots-clés, les liens achetés sur des réseaux de sites sans rapport, le contenu généré en masse sans valeur ajoutée, et les pages de faible qualité espérant surfer sur l'autorité du domaine.\n\n## Conclusion\n\nLe SEO en 2025 récompense ce qui aurait toujours dû être la priorité : créer du contenu utile, écrit par des experts, sur un site rapide et techniquement sain.",
            ],
            [
                'title'             => "Construire une stratégie de contenu qui génère des leads qualifiés",
                'short_description' => "Une stratégie de contenu ne se limite pas à publier régulièrement. Découvrez comment aligner chaque article avec un objectif business précis et mesurer ce qui compte vraiment.",
                'category'          => 'Marketing Digital',
                'content'           => "La plupart des blogs d'entreprise échouent pour la même raison : ils publient du contenu sans stratégie, espérant que la quantité compensera l'absence de direction. Le résultat est un blog qui génère du trafic mais aucun client.\n\n## Partir des objectifs business, pas des idées de sujets\n\nAvant de décider des sujets à traiter, posez-vous la question : quels problèmes mes prospects cherchent-ils à résoudre avant d'acheter mon produit ? Cette question déplace le focus du « qu'est-ce qui nous intéresse » vers « qu'est-ce qui intéresse nos futurs clients ». La différence est radicale.\n\n## Le tunnel de contenu TOFU / MOFU / BOFU\n\nAlignez votre contenu avec les étapes du parcours d'achat. Le Top of Funnel attire une audience large avec du contenu de sensibilisation. Le Middle of Funnel aide les prospects qui comparent des solutions. Le Bottom of Funnel convainc les prospects proches de l'achat avec des études de cas et des témoignages.\n\n## Les métriques qui comptent\n\nOubliez le nombre de vues comme indicateur de succès. Ce qui importe pour un blog orienté business : le taux de conversion vers une inscription ou une demande de démo, le temps sur la page et la profondeur de scroll, les articles qui génèrent des leads, et le revenu attribuable au contenu via UTM tracking.\n\n## La mise à jour du contenu existant\n\nRepublier et mettre à jour un article qui performe déjà est souvent plus rentable qu'en créer un nouveau. Google valorise la fraîcheur, et un article déjà indexé a déjà de l'autorité.\n\n## La fréquence idéale\n\nLa régularité prime sur la fréquence. Un article de fond par semaine, publié le même jour, construit une audience fidèle. Deux articles médiocres par semaine l'érodent.",
            ],


            // ── Design & UX ───────────────────────────────────────────────────
            [
                'title'             => "Design system : pourquoi en créer un et comment bien démarrer",
                'short_description' => "Un design system n'est pas réservé aux grandes entreprises. Même pour une équipe de deux personnes, il accélère la conception et garantit la cohérence visuelle sur la durée.",
                'category'          => 'Design & UX',
                'content'           => "Le terme « design system » intimide souvent les petites équipes qui pensent que c'est un luxe réservé à Google ou Airbnb. C'est une erreur. Un design system adapté à votre contexte — même minimal — change profondément la façon dont produit et développement collaborent.\n\n## Qu'est-ce qu'un design system, exactement ?\n\nUn design system est un ensemble de décisions de design documentées et codifiées : couleurs, typographie, espacement, composants d'interface, règles d'interaction. Il sert de référence unique pour toute l'équipe.\n\nCe n'est pas un Figma bien rangé. C'est la combinaison d'une bibliothèque de composants, d'une documentation vivante, et de guidelines éditoriales.\n\n## Pourquoi en créer un\n\nPour les designers : moins de temps passé à recréer les mêmes composants, plus de temps pour résoudre de vrais problèmes d'expérience. Pour les développeurs : une correspondance directe entre ce qui est dans Figma et ce qui est dans le code. Pour le produit : une cohérence visuelle qui renforce la confiance des utilisateurs.\n\n## Par où commencer\n\nNe commencez pas par créer des composants. Commencez par auditer ce qui existe déjà dans votre interface : recensez les couleurs utilisées en production, listez les tailles de texte et rationalisez-les à 6 ou 7 niveaux maximum, identifiez les composants récurrents.\n\n## Les tokens de design\n\nLes design tokens sont le socle du système : des variables nommées qui représentent vos décisions visuelles. Ils créent le pont entre Figma et le code, et permettent de changer l'apparence globale d'un produit en modifiant quelques valeurs.\n\n## L'erreur à éviter\n\nVouloir tout documenter avant de livrer quoi que ce soit. Un design system se construit dans la continuité du produit, pas à côté.",
            ],
            [
                'title'             => "L'accessibilité web en pratique : au-delà des bonnes intentions",
                'short_description' => "L'accessibilité n'est pas une case à cocher. C'est une discipline qui s'apprend et s'intègre à chaque étape du design. Voici comment passer des principes à la pratique concrète.",
                'category'          => 'Design & UX',
                'content'           => "En France, 12 millions de personnes vivent avec un handicap. Parmi elles, beaucoup utilisent des technologies d'assistance pour naviguer sur le web : lecteurs d'écran, navigation clavier, logiciels de grossissement. Concevoir pour l'accessibilité n'est pas optionnel, c'est concevoir pour tout le monde.\n\n## Les quatre principes WCAG\n\nLe référentiel international WCAG 2.1 structure l'accessibilité autour de quatre principes fondamentaux.\n\nPerceptible : l'information doit être présentée de façon perceptible par tous les sens. Images avec texte alternatif, sous-titres pour les vidéos, contraste suffisant entre le texte et le fond.\n\nUtilisable : l'interface doit fonctionner sans souris. Tout doit être accessible au clavier, les zones de clic doivent être suffisamment grandes (minimum 44x44 px), le focus clavier doit être visible.\n\nCompréhensible : le langage doit être clair, les erreurs de formulaire doivent être explicites, le comportement de l'interface doit être prévisible.\n\nRobuste : le code doit être suffisamment solide pour être interprété correctement par les technologies d'assistance actuelles et futures.\n\n## Les erreurs les plus courantes\n\nBoutons sans texte : une icône seule n'est pas accessible, ajoutez toujours un aria-label. Contraste insuffisant : le ratio minimum est de 4,5:1 pour le texte normal. Focus clavier invisible : beaucoup de designs suppriment l'outline par défaut sans le remplacer, c'est une erreur grave.\n\n## Intégrer l'accessibilité dès le design\n\nL'accessibilité ajoutée après coup coûte 10 fois plus cher que celle intégrée dès la conception. Testez les contrastes directement dans Figma, annotez les maquettes avec les niveaux de titres et les rôles ARIA, et incluez un parcours navigation clavier dans les tests utilisateurs.",
            ],


            // ── Entrepreneuriat ───────────────────────────────────────────────
            [
                'title'             => "Lancer sa première startup en France : les étapes que personne ne vous dit",
                'short_description' => "Créer une startup est passionnant. Mais entre les statuts juridiques, le financement, et la recherche des premiers clients, les obstacles sont nombreux. Voici un retour d'expérience sans filtre.",
                'category'          => 'Entrepreneuriat',
                'content'           => "J'ai lancé ma première startup à 28 ans avec 15 000 euros d'économies et une idée que je pensais révolutionnaire. Trois ans plus tard, j'ai appris plus sur le business en faisant que pendant toutes mes années d'études. Voici ce que j'aurais voulu savoir avant.\n\n## Choisir le bon statut juridique\n\nLa SASU est devenue le statut de référence pour les startups françaises qui souhaitent lever des fonds. Elle offre une séparation nette entre patrimoine personnel et professionnel, et la transformation en SAS est simple une fois que d'autres associés rejoignent le projet.\n\nLa micro-entreprise est idéale pour valider une idée sans frais fixes, mais devient vite contraignante dès que le chiffre d'affaires dépasse les seuils de TVA ou que vous souhaitez embaucher.\n\n## Valider avant de coder\n\nL'erreur classique de l'entrepreneur technique : passer 6 mois à construire un produit avant de parler à un seul client potentiel. La validation doit précéder le développement.\n\nLes méthodes concrètes pour valider rapidement : les entretiens problème (20 conversations avec votre cible), une landing page avec liste d'attente pour mesurer l'intérêt réel, et un prototype soumis à de vrais utilisateurs avant d'écrire la première ligne de code.\n\n## Le financement en France\n\nL'écosystème français est l'un des plus actifs d'Europe. BPI France propose des prêts d'amorçage, garanties bancaires et accompagnement. Le label French Tech offre visibilité et accès aux programmes de financement. Les Business Angels investissent en early stage, souvent couplé à du mentorat.\n\n## L'équipe est tout\n\nAucun investisseur sérieux ne finance une idée. Ils financent une équipe. Trouver un co-fondateur complémentaire (tech + business, ou produit + commercial) multiplie les chances de succès.\n\n## Le conseil le plus important\n\nCommencez à facturer dès que possible. Le premier euro encaissé d'un vrai client apprend plus sur votre produit et votre marché que n'importe quelle étude ou accélérateur.",
            ],
            [
                'title'             => "Productivité et organisation : les systèmes qui fonctionnent vraiment pour les indépendants",
                'short_description' => "Entre la gestion des clients, la production, l'administratif et le développement commercial, le freelance jongle avec tout. Voici les systèmes concrets qui permettent de rester organisé sans s'épuiser.",
                'category'          => 'Entrepreneuriat',
                'content'           => "La liberté du statut d'indépendant a un revers : personne ne structure votre temps à votre place. Sans système, les semaines s'enchaînent dans un sentiment permanent d'urgence et d'inachevé. Avec un bon système, la même quantité de travail produit deux fois plus de résultats.\n\n## Le time blocking : bloquer le temps avant qu'il soit pris\n\nLe principe est simple : chaque type de travail a son créneau dédié dans la semaine. Production client le matin (quand l'énergie est haute), emails et administratif en milieu de journée, développement commercial en fin d'après-midi.\n\nCe que cela évite : la dispersion constante entre des tâches de nature différente, qui fragmentent la concentration et épuisent cognitivement.\n\n## La règle du « single next action »\n\nTirée du GTD (Getting Things Done) de David Allen : pour chaque projet ou tâche, identifiez toujours la prochaine action physique concrète. Non pas « travailler sur la proposition client » mais « ouvrir le document et rédiger l'introduction de la section budget ».\n\nLes projets ne se bloquent pas sur le travail. Ils se bloquent sur le manque de clarté sur la prochaine étape.\n\n## Le weekly review : 45 minutes qui changent tout\n\nChaque vendredi, 45 minutes pour vider la boîte mail, revoir tous les projets en cours, planifier les blocs de la semaine suivante, et mesurer les métriques importantes (CA, propositions envoyées, nouveaux contacts).\n\n## Les outils\n\nNotion pour la gestion des projets et la documentation. Toggl pour le suivi du temps. Pennylane pour la facturation. L'outil parfait n'existe pas — le meilleur système est celui que vous utilisez réellement.\n\n## La discipline de la déconnexion\n\nParadoxalement, les indépendants les plus productifs sont ceux qui ont appris à s'arrêter. Travailler 10 heures par jour 6 jours sur 7 n'est pas de la performance, c'est de l'endurance qui finit par casser.",
            ],


            // ── Intelligence Artificielle ─────────────────────────────────────
            [
                'title'             => "Intégrer l'IA dans votre workflow de développement : guide pratique 2025",
                'short_description' => "Les outils d'IA ne remplacent pas les développeurs, ils amplifient leur productivité. Voici comment intégrer concrètement GitHub Copilot, Claude et les LLM dans votre quotidien technique.",
                'category'          => 'Intelligence Artificielle',
                'content'           => "En 2025, un développeur qui n'utilise pas d'outils IA est dans la même situation qu'un développeur qui refusait d'utiliser Git en 2010 : pas handicapé, mais moins efficace que ses pairs. La question n'est plus « faut-il adopter l'IA ? » mais « comment l'adopter intelligemment ? ».\n\n## Les cas d'usage où l'IA excelle\n\nGénération de code boilerplate : tests unitaires, migrations de base de données, fichiers de configuration, code répétitif. L'IA génère en secondes ce qui prenait 10 minutes.\n\nCompréhension de code inconnu : coller un fichier de 300 lignes et demander d'expliquer ce que fait une fonction est infiniment plus rapide que de le déchiffrer seul.\n\nRefactoring et revue de code : demander de réécrire une fonction en respectant le principe de responsabilité unique ou d'identifier les problèmes potentiels dans un bloc de code.\n\nDocumentation : générer la JSDoc ou la PHPDoc d'une fonction existante, rédiger un README à partir d'un code source.\n\n## Les cas d'usage où l'IA échoue\n\nL'IA hallucine sur des APIs ou des bibliothèques récentes qu'elle ne connaît pas. Elle génère du code fonctionnel en apparence mais avec des failles de sécurité subtiles.\n\nLa règle : ne jamais copier-coller du code généré sans le comprendre. L'IA est un pair-programmeur rapide, pas un architecte infaillible.\n\n## Construire un contexte efficace\n\nLa qualité d'une réponse IA est directement proportionnelle à la qualité du prompt. Pour du code, cela signifie fournir le langage et la version exacte, partager les conventions du projet, décrire les contraintes techniques, et montrer un exemple de code existant dans le projet.\n\n## Les RAG pour des assistants métier\n\nLes Retrieval-Augmented Generation permettent de connecter un LLM à votre documentation interne, votre base de code ou vos spécifications produit. Le résultat : un assistant qui répond en tenant compte de votre contexte spécifique. C'est la prochaine évolution pour les équipes qui veulent industrialiser l'usage de l'IA.",
            ],
            [
                'title'             => "Comprendre les LLM sans être data scientist : ce que tout développeur doit savoir",
                'short_description' => "Vous utilisez ChatGPT ou Claude chaque jour, mais comprenez-vous comment ils fonctionnent ? Ce guide explique les concepts clés des grands modèles de langage de façon accessible et sans mathématiques.",
                'category'          => 'Intelligence Artificielle',
                'content'           => "Les grands modèles de langage (LLM) sont devenus des outils du quotidien sans que la plupart des développeurs comprennent réellement ce qui se passe sous le capot. Ce n'est pas indispensable pour les utiliser, mais comprendre les concepts fondamentaux aide à les utiliser mieux et à éviter les erreurs courantes.\n\n## Ce qu'est vraiment un LLM\n\nUn LLM est un modèle statistique entraîné à prédire le prochain token dans une séquence de texte. L'entraînement se fait sur des quantités astronomiques de texte (le web entier, des livres, du code source), ce qui permet au modèle de compresser une représentation statistique du langage humain et des connaissances qu'il contient.\n\n## La fenêtre de contexte\n\nLa fenêtre de contexte est la quantité de texte qu'un modèle peut « voir » à un instant donné. Pourquoi c'est important ? Le modèle ne mémorise rien entre les conversations. Chaque appel est indépendant. Si vous voulez que le modèle sache quelque chose, il faut que cette information soit dans la fenêtre de contexte.\n\n## Les hallucinations : pourquoi elles arrivent\n\nUn LLM ne cherche pas dans une base de données. Il génère une réponse probable basée sur ses paramètres. Quand il ne sait pas, il génère quand même quelque chose de plausible. Les hallucinations sont plus fréquentes sur des données récentes, des noms propres, des chiffres précis, et des API peu représentées dans les données d'entraînement.\n\n## Temperature et sampling\n\nLe paramètre temperature contrôle le caractère aléatoire de la génération. Pour du code, une température basse est préférable. Pour de la rédaction créative, une température plus haute donne de meilleurs résultats.\n\n## Fine-tuning vs prompting\n\nFine-tuner un modèle coûte cher et est réservé à des cas très spécifiques. Pour 95 % des besoins métier, un bon prompting système et une architecture RAG bien conçue donnent des résultats équivalents.",
            ],


            // ── Cybersécurité ─────────────────────────────────────────────────
            [
                'title'             => "Les vulnérabilités web les plus exploitées en 2025 et comment s'en protéger",
                'short_description' => "L'OWASP Top 10 reste la référence pour sécuriser les applications web. Voici les vulnérabilités actuellement les plus ciblées par les attaquants et les contre-mesures concrètes à mettre en place.",
                'category'          => 'Cybersécurité',
                'content'           => "La sécurité d'une application web n'est pas un état qu'on atteint, c'est une discipline qu'on pratique en continu. Les attaquants automatisent leurs scans, cherchent en permanence des failles connues non corrigées, et exploitent l'erreur humaine.\n\n## Injection SQL\n\nToujours présente malgré des décennies de sensibilisation. La protection est simple : n'utilisez jamais de requêtes SQL construites par concaténation de chaînes. Utilisez systématiquement des requêtes préparées ou un ORM. Les frameworks modernes comme Laravel ou Django le font par défaut, mais il faut rester vigilant lors des requêtes brutes.\n\n## Cross-Site Scripting (XSS)\n\nL'injection de JavaScript dans des pages web consultées par d'autres utilisateurs. Se protège par l'échappement systématique des données affichées et une Content Security Policy (CSP) bien configurée.\n\n## Broken Authentication\n\nMots de passe faibles, tokens non expirés, absence de rate limiting sur les endpoints de login. Utilisez bcrypt ou argon2 pour les mots de passe, invalidez les tokens lors de la déconnexion, et limitez les tentatives d'authentification.\n\n## Exposition de données sensibles\n\nDonnées personnelles transmises en HTTP, logs applicatifs contenant des tokens ou des mots de passe, API retournant plus d'informations que nécessaire. HTTPS est non-négociable. Filtrez précisément ce que vos API exposent.\n\n## IDOR (Insecure Direct Object References)\n\nUn utilisateur peut accéder aux ressources d'un autre en changeant un ID dans l'URL. La contre-mesure : vérifier systématiquement que l'utilisateur authentifié est bien propriétaire de la ressource demandée, jamais juste que la ressource existe.\n\n## La règle des moindres privilèges\n\nChaque composant ne doit avoir accès qu'à ce dont il a strictement besoin. Un utilisateur de base de données dédié à l'application ne devrait jamais avoir les droits DROP ou CREATE TABLE en production.\n\n## Les outils de détection\n\nOWASP ZAP est un scanner de vulnérabilités open source. Snyk détecte les vulnérabilités dans les dépendances. SonarQube réalise une analyse statique de code pour les failles de sécurité. La sécurité intégrée dès le développement (DevSecOps) est infiniment moins coûteuse que la sécurité réparatrice après une intrusion.",
            ],

        ]; // fin getArticlesData()
    }

    private function getCommentsData(): array
    {
        return [
            // Article 0 - React 19
            ['name' => 'Maxime Fontaine',  'email' => 'maxime.fontaine@gmail.com',       'message' => "Excellent article. Le compilateur React m'a déjà sauvé la mise sur un projet Next.js. Plus besoin de se battre avec les dépendances manquantes de useMemo.", 'article_index' => 0],
            ['name' => 'Inès Carpentier',  'email' => 'ines.c@protonmail.com',            'message' => "La fonction use() avec Suspense est vraiment élégante. J'ai testé sur un side project et ça simplifie énormément la gestion des données asynchrones côté composant.", 'article_index' => 0],
            ['name' => 'Romain Gilles',    'email' => 'romain.gilles@outlook.fr',        'message' => "Merci pour le résumé clair. Est-ce que les Actions fonctionnent avec React Native ou c'est uniquement pour le web ?", 'article_index' => 0],
            // Article 1 - Laravel API
            ['name' => 'Aurore Petit',     'email' => 'aurore.petit@live.fr',             'message' => "La partie sur les Form Requests m'a convaincu de refactoriser mes controllers. C'est tellement plus lisible quand la validation est déléguée.", 'article_index' => 1],
            ['name' => 'Kévin Morel',      'email' => 'kmorel.dev@gmail.com',             'message' => "Très bon tour d'horizon. J'ajouterais l'importance des tests de feature avec PHPUnit pour valider le comportement de l'API avant chaque déploiement.", 'article_index' => 1],
            // Article 2 - Tailwind v4
            ['name' => 'Juliette Renaud',  'email' => 'juliette.renaud@yahoo.fr',        'message' => "La config CSS-first est un vrai soulagement. Sur notre projet multi-frameworks, ça évite d'avoir la config JS dans un endroit bizarre.", 'article_index' => 2],
            ['name' => 'Antoine Mercier',  'email' => 'a.mercier@hotmail.fr',             'message' => "Le gain de performance avec le nouveau moteur est impressionnant. Sur notre monorepo de 200+ composants, le cold start est passé de 8 secondes à moins de 2.", 'article_index' => 2],
            // Article 3 - SEO
            ['name' => 'Stéphanie Brun',   'email' => 'stephanie.brun@agence-seo.fr',   'message' => "L'INP qui remplace le FID depuis mars 2024 est encore sous-estimé par beaucoup d'agences. Bon article de synthèse pour 2025.", 'article_index' => 3],
            ['name' => 'Pierre-Yves Gautier','email' => 'pygautier@gmail.com',           'message' => "La section E-E-A-T est la plus importante selon moi. Google devient de plus en plus capable de distinguer le contenu généré en masse du contenu expert.", 'article_index' => 3],
            ['name' => 'Nadia Lefèvre',    'email' => 'nlefevre@consulting-digital.com', 'message' => "Très bien écrit. Une précision sur les Core Web Vitals : l'INP sera bien le signal principal dès cette année et beaucoup de sites ne sont pas encore optimisés.", 'article_index' => 3],
            // Article 4 - Stratégie contenu
            ['name' => 'Benjamin Perrin',  'email' => 'b.perrin@startup-lyon.fr',        'message' => "On a appliqué le modèle TOFU/MOFU/BOFU sur notre blog B2B et les leads qualifiés ont augmenté de 40% en 6 mois. Le contenu BOFU (études de cas) convertit beaucoup mieux qu'on ne le pensait.", 'article_index' => 4],
            // Article 5 - Design System
            ['name' => 'Chloé Vidal',      'email' => 'chloe.vidal@designer-ux.fr',     'message' => "L'audit comme point de départ est une excellente approche. On a identifié 47 couleurs différentes dans notre app mobile en faisant cet exercice. Édifiant.", 'article_index' => 5],
            ['name' => 'Florian Dupuy',    'email' => 'florian.dupuy@gmail.com',         'message' => "La section sur les design tokens est au cœur du sujet. Depuis qu'on les utilise pour faire le pont Figma-code, les itérations sont beaucoup plus rapides.", 'article_index' => 5],
            // Article 6 - Accessibilité
            ['name' => 'Lucie Hamelin',    'email' => 'lucie.hamelin@accessibilite.org', 'message' => "Merci de parler d'accessibilité de façon concrète et non culpabilisante. La mention du RGAA est importante pour le contexte français, notamment pour les services publics.", 'article_index' => 6],
            ['name' => 'Sébastien Collet', 'email' => 'seb.collet@dev-inclusif.fr',      'message' => "Le focus visible est vraiment l'erreur la plus courante. J'aurais ajouté la gestion des formulaires d'erreur qui doit ramener le focus sur le message d'erreur pour les lecteurs d'écran.", 'article_index' => 6],
            // Article 7 - Startup France
            ['name' => 'Pauline Chevalier','email' => 'pauline.chevalier@entrepreneur.fr','message' => "Le conseil sur la validation avant de coder est celui que j'aurais aimé recevoir il y a 3 ans. On a passé 8 mois à construire quelque chose que personne ne voulait acheter.", 'article_index' => 7],
            ['name' => 'Marc-Antoine Faure','email' => 'mafaure@bpifrance-alumni.fr',    'message' => "Très bon article. Pour compléter la partie financement, les concours d'innovation type i-Nov et i-Lab de BPI sont une source de financement non-dilutif souvent ignorée.", 'article_index' => 7],
            // Article 8 - Productivité freelance
            ['name' => 'Eléonore Simonin', 'email' => 'eleonore.simonin@freelance.fr',  'message' => "Le weekly review a changé ma façon de travailler. Je le fais le jeudi soir plutôt que le vendredi pour ne pas terminer la semaine avec des tâches ouvertes.", 'article_index' => 8],
            // Article 9 - IA workflow dev
            ['name' => 'Thibault Grimaud', 'email' => 'thibault.grimaud@tech.io',        'message' => "La mise en garde sur le copier-coller sans comprendre est essentielle. J'ai vu des failles de sécurité introduites par des développeurs juniors qui faisaient confiance aveuglément au code généré.", 'article_index' => 9],
            ['name' => 'Mélanie Jacquet',  'email' => 'mjacquet@datanomad.fr',           'message' => "La section sur les RAG est la plus intéressante. On a mis en place un assistant RAG sur notre documentation interne et le gain de temps pour l'onboarding des nouveaux développeurs est spectaculaire.", 'article_index' => 9],
            // Article 10 - LLM
            ['name' => 'Arthur Leconte',   'email' => 'arthur.leconte@ia-accessible.fr', 'message' => "Excellente vulgarisation. La distinction fine-tuning vs RAG est souvent mal comprise et votre explication est la plus claire que j'ai lue en français.", 'article_index' => 10],
            // Article 11 - Cybersécurité
            ['name' => 'Vincent Barbier',  'email' => 'v.barbier@cybersec-pro.fr',       'message' => "L'IDOR est vraiment la vulnérabilité la plus fréquente dans les audits que je réalise. Les développeurs pensent souvent que l'authentification suffit, sans vérifier l'autorisation sur chaque ressource.", 'article_index' => 11],
            ['name' => 'Isabelle Morin',   'email' => 'imorin@ssi-conseil.fr',           'message' => "Bon article introductif. J'ajouterais l'importance des en-têtes de sécurité HTTP : HSTS, X-Frame-Options, X-Content-Type-Options. Souvent oubliés et pourtant configurables en 10 minutes.", 'article_index' => 11],
        ];
    }
}
