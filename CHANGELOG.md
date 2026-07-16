# Changelog

Toutes les évolutions documentaires du projet APPART.SN REBUILD sont consignées dans ce fichier.

## [Sprint J0 — Foundation v1.0] — 2026-07-16

### Ajouté

- Création du projet sous PHP 8.5.x et Laravel 13.x, avec PostgreSQL 18.x comme plateforme de persistance cible.
- Création de la structure du monolithe modulaire et des treize enveloppes de domaines vides.
- Séparation physique du domaine (`src/`) et de la périphérie Laravel (`app/`).
- Configuration du socle qualité avec Pint, Larastan, PHPUnit, audit des dépendances et tests d’architecture.
- Ajout de protections vérifiant les treize modules, la structure validée et l’indépendance du domaine vis-à-vis de Laravel.
- Configuration locale minimale sans création de base, de schéma ou de migration.

### Contraintes respectées

- Aucune fonctionnalité métier développée.
- Aucun Aggregate, modèle métier, contrôleur métier ou API métier créé.
- Aucune règle de publication, SEO, paiement, authentification ou migration Legacy implémentée.
- Aucun secret réel ajouté au dépôt.

## [Sprint 7 — Document 5 : ADR-1005 Repository Governance] — 2026-07-16

### Ajouté

- Création de `docs/implementation/ADR-1005-REPOSITORY-GOVERNANCE.md`.
- Adoption d’une branche `main` protégée et de branches de travail courtes, sans branche `develop` permanente.
- Définition des conventions de commit et des Pull Requests obligatoires.
- Classification des changements selon quatre niveaux de risque et formalisation des revues associées.
- Définition des Quality Gates universels, métier, architecture, données et sécurité.
- Formalisation de la CI conceptuelle, des protections de branches et de la fusion par squash.
- Définition des règles de retour, urgence, Definition of Ready et Definition of Done.
- Renforcement de la politique de dette technique et de gouvernance des dépendances.
- Documentation des mesures, critères d’acceptation, questions ouvertes et références aux ADR validés.

### Contraintes respectées

- Livrable exclusivement documentaire.
- Aucun code ni projet Laravel créé.
- Aucune configuration GitHub ou protection de branche réelle créée.
- Aucun pipeline ou configuration CI créé.
- Aucun document normatif existant modifié.
- Seuls le nouvel ADR et le présent changelog ont été modifiés.

## [Sprint 7 — Document 4 : ADR-1004 Authentication and Secrets] — 2026-07-16

### Ajouté

- Création de `docs/implementation/ADR-1004-AUTHENTICATION-AND-SECRETS.md`.
- Définition du modèle d’identité séparant Compte, Professionnel, Mandat, comptes internes et Système.
- Décision d’une authentification web par session et de niveaux d’assurance proportionnés aux acteurs.
- Formalisation des politiques de mots de passe, MFA, résistance au phishing, sessions et step-up.
- Définition de l’autorisation contextuelle par action, ressource, ownership, état et quatre yeux.
- Définition de la séparation des environnements et d’une capacité dédiée de gestion des secrets.
- Formalisation des rotations, récupérations d’accès, comptes techniques, identités applicatives et accès d’urgence.
- Documentation de la journalisation, réponse aux incidents, risques, critères d’acceptation et questions ouvertes.
- Traçabilité vers ADR-1000, ADR-1001, ADR-1002 et ADR-1003.

### Contraintes respectées

- Livrable exclusivement conceptuel et documentaire.
- Aucun code ni projet Laravel créé.
- Aucune commande Composer lancée.
- Aucun package, fichier `.env`, configuration ou secret créé.
- Aucun document normatif existant modifié.
- Seuls le nouvel ADR et le présent changelog ont été modifiés.

## [Sprint 7 — Document 3 : ADR-1003 Physical Module Structure] — 2026-07-16

### Ajouté

- Création de `docs/implementation/ADR-1003-PHYSICAL-MODULE-STRUCTURE.md`.
- Séparation officielle du cœur indépendant sous `src` et de la périphérie Laravel sous `app`.
- Positionnement des treize domaines sous `src/Modules` avec enveloppes J0 visibles et vides.
- Définition des positions futures des interfaces, adaptateurs, projections, tests, documentation et ADR.
- Isolement des outils temporaires sous `tools/temporary` et de Migration Legacy dans des zones supprimables.
- Formalisation des exclusions du socle partagé, dépendances physiques et règles de visibilité.
- Définition des politiques futures d’espaces de noms, nommage et évolution structurelle.
- Documentation des critères d’acceptation et questions ouvertes avant J0.

### Contraintes respectées

- Livrable exclusivement conceptuel et documentaire.
- Aucun projet Laravel ni dossier applicatif créé.
- Aucune commande Composer lancée.
- Aucun code, fichier de configuration, migration ou package créé.
- Aucun espace de noms définitif imposé.
- Aucun document normatif existant modifié.
- Seuls le nouvel ADR et le présent changelog ont été modifiés.

## [Sprint 7 — Document 2 : ADR-1002 Initial Persistence Platform] — 2026-07-16

### Ajouté

- Création de `docs/implementation/ADR-1002-INITIAL-PERSISTENCE-PLATFORM.md`.
- Sélection officielle de PostgreSQL 18.x comme plateforme relationnelle principale.
- Comparaison argumentée de PostgreSQL, MySQL et MariaDB sur intégrité, concurrence, transactions, contraintes, indexation, JSON, recherche et exploitation.
- Définition des politiques de transaction, intégrité, contraintes, concurrence, sauvegarde et restauration.
- Formalisation des règles d’évolution, données historiques, Migration Legacy, performance et sécurité.
- Reconnaissance de MySQL 8.4 LTS comme alternative de repli conditionnelle.
- Documentation des conséquences, risques, critères d’acceptation et questions ouvertes.
- Traçabilité explicite vers ADR-1000 et ADR-1001.

### Contraintes respectées

- Livrable exclusivement conceptuel et documentaire.
- Aucun code ni projet Laravel créé.
- Aucune commande Composer lancée.
- Aucune base de données, table, structure SQL, migration Laravel, modèle Eloquent ou fichier de configuration créé.
- Aucun document normatif existant modifié.
- Seuls le nouvel ADR et le présent changelog ont été modifiés.

## [Sprint 7 — Document 1 : ADR-1001 Runtime and Framework Selection] — 2026-07-16

### Ajouté

- Création de `docs/implementation/ADR-1001-RUNTIME-AND-FRAMEWORK-SELECTION.md`.
- Sélection officielle de PHP 8.5.x et Laravel 13.x pour le futur projet.
- Comparaison des branches PHP 8.2 à 8.5 et Laravel 11 à 13 selon les calendriers officiels.
- Vérification de la compatibilité PHP/Laravel et définition d’un repli PHP 8.4 strictement conditionnel.
- Formalisation des politiques de support, mise à niveau, rétrocompatibilité, LTS, fin de support et corrections de sécurité.
- Définition des politiques relatives aux dépendances PHP, extensions et packages Laravel.
- Documentation des risques, alternatives, conséquences, critères d’acceptation et questions ouvertes.
- Traçabilité explicite vers ADR-1000 et les décisions techniques restant ouvertes.

### Contraintes respectées

- Livrable exclusivement documentaire.
- Aucun code ni projet Laravel créé.
- Aucune commande Composer lancée.
- Aucune migration, API, structure physique ou fichier de configuration créé.
- Aucun document normatif existant modifié.
- Seuls le nouvel ADR et le présent changelog ont été modifiés.

## [Sprint 6 — Laravel Project Plan] — 2026-07-16

### Ajouté

- Création de `docs/implementation/LARAVEL-PROJECT-PLAN.md`.
- Décision du nom officiel, de l’identifiant technique et de l’arborescence générale future.
- Définition des treize enveloppes de domaines créées vides à J0.
- Formalisation de l’ordre exact de création du futur projet et des portes de blocage.
- Distinction entre composants Laravel immédiats et capacités volontairement différées.
- Décision de l’ordre initial : Géographie, Identité et accès, puis Administration et audit.
- Définition de la séquence complète des treize domaines et de leurs prérequis.
- Formalisation des critères autorisant la première ligne de code et des contrôles précédant le premier commit.
- Documentation des risques J0, critères d’acceptation et questions ouvertes.

### Contraintes respectées

- Livrable exclusivement préparatoire et documentaire.
- Aucun code ni projet Laravel créé.
- Aucune commande Composer lancée.
- Aucun contrôleur, modèle, migration, API, package ou fichier de configuration créé.
- Aucun document normatif existant modifié.
- Seuls le nouveau Laravel Project Plan et le présent changelog ont été modifiés.

## [Sprint 5 — Document 1 : ADR-1000 Technical Foundation] — 2026-07-16

### Ajouté

- Création de `docs/implementation/ADR-1000-TECHNICAL-FOUNDATION.md`.
- Confirmation du monolithe modulaire, de l’indépendance du Domaine et des frontières validées.
- Définition des critères de sélection et de version pour PHP, Laravel, base de données, cache, Recherche, Queue, médias, tests et observabilité.
- Formalisation des principes futurs de modularité, organisation, nommage, dépendances et configuration.
- Définition des politiques de secrets, journalisation, erreurs et évolutions applicatives.
- Définition des exigences de qualité, revue, tests, performance, sécurité, packages tiers et dette technique.
- Création des critères imposant de futurs ADR et d’une roadmap technique progressive.
- Documentation des décisions ouvertes, risques majeurs, critères d’acceptation et questions ouvertes.

### Contraintes respectées

- Livrable exclusivement documentaire, sans description de réalisation.
- Aucun code ni projet Laravel créé.
- Aucune commande Composer lancée.
- Aucun fichier de configuration, migration exécutable ou API créé.
- Aucun document normatif existant modifié.
- Seuls le nouvel ADR et le présent changelog ont été modifiés.

## [Sprint 4 — Document 3 : Aggregate Boundaries] — 2026-07-16

### Ajouté

- Création de `docs/architecture/AGGREGATE-BOUNDARIES.md`.
- Classement des candidats du Domain Mapping entre Aggregate Roots retenus, candidats et refusés.
- Définition des responsabilités, propriétaires, invariants, frontières, tailles et cycles de vie des vingt Roots retenues.
- Formalisation des Entités internes, Value Objects, références par identité, compositions et dépendances interdites.
- Définition des cohérences immédiate et différée, transactions métier conceptuelles et événements inter-domaines.
- Justification détaillée de la séparation entre Annonce, Cycle de vie, Modération, SEO et Recherche.
- Confirmation du caractère temporaire de Migration Legacy et définition de ses conditions de clôture.
- Documentation des critères de fusion et division futures, risques, anti-patterns, critères d’acceptation et questions ouvertes.

### Contraintes respectées

- Livrable exclusivement conceptuel et documentaire.
- Aucun code ni élément de réalisation créé.
- Aucun document normatif existant modifié.
- Seuls le nouveau document Aggregate Boundaries et le présent changelog ont été modifiés.

## [Sprint 4 — Document 2 : Domain Mapping] — 2026-07-16

### Ajouté

- Création de `docs/architecture/DOMAIN-MAPPING.md`.
- Cartographie conceptuelle des treize domaines, de leurs responsabilités, propriétaires, concepts et invariants.
- Identification des candidats Aggregate Roots, Entités et Value Objects sans structure physique.
- Définition des événements, commandes, requêtes, politiques et interactions en langage métier.
- Formalisation des dépendances autorisées et interdites, des frontières de cohérence et de l’ownership unique.
- Création du langage partagé et de la correspondance avec les documents normatifs.
- Documentation des frontières fragiles, risques de duplication, critères d’acceptation et questions ouvertes.

### Contraintes respectées

- Livrable exclusivement conceptuel et documentaire.
- Aucun code ni élément de réalisation créé.
- Aucun document normatif existant modifié.
- Seuls le nouveau Domain Mapping et le présent changelog ont été modifiés.

## [Sprint 4 — Document 1 : Architecture Blueprint] — 2026-07-16

### Ajouté

- Création de `docs/architecture/ARCHITECTURE-BLUEPRINT.md`.
- Comparaison des styles architecturaux réalistes et recommandation d'un monolithe modulaire orienté domaines avec principes de ports et adaptateurs.
- Définition de treize domaines fonctionnels, de leurs responsabilités, dépendances, priorités et statuts.
- Formalisation des frontières de modules, couches logiques et modèles d'interaction.
- Traduction architecturale du cycle de vie des annonces, de la politique média, des permissions et du SEO.
- Séparation des modèles de lecture, de l'administration et de la migration Legacy.
- Définition des principes de sécurité, performance, observabilité, audit et tests.
- Documentation des risques architecturaux et des décisions techniques encore ouvertes.

### Contraintes respectées

- Aucun code applicatif créé.
- Aucun projet Laravel créé.
- Aucune commande Composer lancée.
- Aucun modèle Eloquent, migration SQL, API détaillée ou contrôleur créé.
- Aucun fichier de configuration technique créé.
- Aucun document normatif existant modifié.

## [Sprint 3 — Document 5 : Migration Rules] — 2026-07-16

### Ajouté

- Création de `docs/MIGRATION-RULES.md`.
- Définition des principes, sources de vérité, règles de qualification, nettoyage et déduplication.
- Matrices Conserver, Nettoyer, Fusionner, Archiver et Supprimer pour chaque domaine métier.
- Formalisation des règles propres aux comptes, professionnels, annonces, médias, géographie, SEO, paiements, favoris, signalements, contenus et paramètres métier.
- Définition des données non migrées, archivées et supprimées.
- Encadrement du rapprochement des volumes, des validations métier et de la gestion des erreurs.
- Définition du plan de migration, du plan de retour et des critères d'acceptation.
- Création du cadre officiel du registre des décisions de migration.

### Contraintes respectées

- Document exclusivement métier.
- Chaque disposition est justifiée par domaine.
- Aucun code applicatif ni mécanisme de réalisation créé.

## [Sprint 3 — Document 4 : SEO Policy] — 2026-07-16

### Ajouté

- Création de `docs/SEO-POLICY.md`.
- Définition des objectifs, principes directeurs et règles de gouvernance SEO.
- Formalisation des politiques propres aux neuf types de pages publiques.
- Définition des conditions d'indexation, de non-indexation, de canonical et de redirection.
- Reconnaissance des URL historiques comme patrimoine soumis à un registre de décisions.
- Définition du traitement SEO de chaque état d'annonce.
- Encadrement du maillage, des fils d'Ariane, des données structurées, des sitemaps, de la politique Robots, de la pagination et des facettes.
- Définition des règles relatives aux soft-404, pages pauvres, duplications, qualité minimale et migration.
- Documentation des critères d'acceptation et questions ouvertes.

### Contraintes respectées

- Document exclusivement métier et fonctionnel.
- Le référencement reste subordonné aux règles métier.
- Aucune annonce non publiée ne peut être indexée.
- Aucun code applicatif ni mécanisme de réalisation créé.

## [Sprint 3 — Document 3 : Permissions Matrix] — 2026-07-16

### Ajouté

- Création de `docs/PERMISSIONS-MATRIX.md`.
- Définition des neuf acteurs officiels, de leurs responsabilités et de leurs interdictions.
- Matrice complète des dix actions métier pour quatorze ressources.
- Formalisation des permissions spéciales, séparations de responsabilités et validations à quatre yeux.
- Définition des actions obligatoirement journalisées et des informations de traçabilité attendues.
- Documentation des actions interdites, cas exceptionnels, critères d'acceptation et questions ouvertes.

### Contraintes respectées

- Document exclusivement métier.
- Permissions définies par action et par ressource, jamais par écran.
- Aucun code applicatif créé.
- Aucun mécanisme de réalisation défini.

## [Sprint 3 — Document 2 : Media Policy] — 2026-07-16

### Ajouté

- Création de `docs/MEDIA-POLICY.md`.
- Définition des médias autorisés, des images obligatoires et des règles d'image principale et d'ordre.
- Formalisation des exigences de format, qualité, dimensions, rotation, compression et variantes fonctionnelles.
- Définition des règles relatives aux contenus interdits, doublons, médias orphelins, métadonnées et droits d'utilisation.
- Encadrement des suppressions, remplacements, archives et durées de conservation.
- Définition des politiques propres aux professionnels, contenus éditoriaux, référencement, accessibilité et modération.
- Documentation des cas particuliers, critères d'acceptation et questions ouvertes.

### Contraintes respectées

- Document exclusivement métier et fonctionnel.
- Aucun code applicatif créé.
- Aucun mécanisme de réalisation défini.

## [Sprint 3 — Document 1 : Listing Lifecycle] — 2026-07-16

### Ajouté

- Création de `docs/LISTING-LIFECYCLE.md`.
- Définition des dix états officiels d'une annonce et de leurs transitions autorisées.
- Attribution des responsabilités entre annonceur, modérateur, super administrateur, commercial, responsable SEO et système.
- Formalisation des déclencheurs, notifications et effets sur le SEO, la recherche, l'administration et les statistiques.
- Définition des règles d'annulation, d'archivage, d'expiration et de renouvellement.
- Documentation des cas exceptionnels, critères d'acceptation et ambiguïtés restant à arbitrer.

### Contraintes respectées

- Document exclusivement métier.
- Aucun code applicatif créé.
- Aucun framework choisi ou créé.
- Aucune base de données créée ou définie.
- Aucune API créée ou définie.

## [Sprint 2 — Master Blueprint] — 2026-07-16

### Ajouté

- Création de `docs/MASTER-BLUEPRINT.md`.
- Définition de la vision, des objectifs et de l'architecture fonctionnelle du nouveau APPART.SN.
- Cartographie des modules obligatoires, conditionnels et exclus.
- Définition des navigations publique, utilisateur et administrative.
- Formalisation des orientations SEO, sécurité, performance et migration.
- Proposition d'une roadmap, de critères d'acceptation, d'un registre de risques et des questions ouvertes.

### Contraintes respectées

- Aucun code applicatif créé.
- Aucun projet Laravel ou autre framework créé.
- Aucune base de données créée.
- Aucune API définie ou créée.
- Aucune reprise de l'architecture technique Legacy.
