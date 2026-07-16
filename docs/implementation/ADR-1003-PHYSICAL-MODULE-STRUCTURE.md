# ADR-1003 — Structure physique des modules

## Statut de la décision

- **Projet :** APPART.SN REBUILD 2026
- **Sprint :** 7 — Document 3
- **Date de décision :** 16 juillet 2026
- **Statut :** proposé pour validation
- **Portée :** organisation physique future du dépôt et des modules
- **Décisions héritées :** PHP 8.5.x, Laravel 13.x et PostgreSQL 18.x
- **Hors périmètre :** création du projet, création de dossiers, espaces de noms définitifs, code, configuration, migrations et packages

## Décision directrice

Le futur dépôt séparera physiquement :

- le cœur indépendant dans `src` ;
- la périphérie Laravel dans `app` ;
- les preuves dans `tests` ;
- la gouvernance dans `docs` ;
- les outils temporaires dans `tools/temporary`.

Les treize domaines seront visibles sous `src/Modules`. Les interfaces, adaptateurs et projections ne seront pas placés dans le cœur des modules. Cette organisation sera matérialisée lors du Sprint J0, jamais par le présent ADR.

---

# 1. Objet

Cet ADR définit l’organisation physique officielle qui devra être créée lors du Sprint J0. Il fixe les zones, responsabilités, directions de dépendance, règles de visibilité et politiques d’évolution.

Il ne choisit pas les espaces de noms définitifs et ne produit aucun artefact applicatif. Les chemins cités désignent des emplacements futurs approuvés, non des éléments actuellement créés.

---

# 2. Principes d’organisation

1. **Le métier avant le framework.** Les domaines occupent une zone indépendante de Laravel.
2. **Treize domaines visibles.** Aucun domaine ne disparaît dans une couche technique globale.
3. **Périphérie explicite.** Interfaces, persistance et capacités externes restent hors du cœur.
4. **Ownership physique.** Tout fichier futur possède un domaine ou une capacité propriétaire identifiable.
5. **Dépendances dirigées.** La périphérie dépend du cœur ; le cœur ne dépend jamais de la périphérie.
6. **Contrats publics minimaux.** Un module n’expose que les intentions, lectures et faits approuvés.
7. **Aucun accès latéral interne.** Un module ne traverse pas la structure interne d’un autre.
8. **Lectures séparées.** Recherche, SEO, administration et statistiques ne deviennent pas sources de vérité.
9. **Partagé exceptionnel.** Un élément n’entre dans le socle partagé que s’il est sans règle métier et stable pour plusieurs domaines.
10. **Tests alignés sur les frontières.** Les preuves du Domaine ne requièrent ni Laravel ni PostgreSQL.
11. **Legacy temporaire.** Migration Legacy est isolée, identifiable et supprimable.
12. **Structure contrôlable.** Les règles doivent pouvoir être vérifiées automatiquement au Sprint J0.
13. **Pas d’architecture spéculative.** Aucune zone n’est créée pour une capacité non validée.

---

# 3. Structure générale du dépôt

## 3.1 Zones de premier niveau futures

| Emplacement futur | Rôle officiel | Autorité |
|---|---|---|
| `src` | cœur métier, cas d’usage et contrats indépendants | documents de domaines et Aggregates |
| `app` | périphérie Laravel, interfaces, adaptateurs, projections et amorçage | ADR techniques et contrats du cœur |
| `tests` | preuves du Domaine, orchestration, intégrations, architecture et qualités | critères d’acceptation |
| `docs` | normes, ADR, décisions, guides et registres | gouvernance documentaire |
| `tools` | outils hors exécution courante, strictement gouvernés | besoin opérationnel approuvé |
| zones standard Laravel | amorçage minimal exigé par le framework | Laravel, sans règle métier |
| zones opérationnelles futures | déploiement, automatisation et exploitation si décidés | ADR ultérieurs |

## 3.2 Vue logique de la future arborescence

- `src`
  - `Modules`
    - treize enveloppes métier
  - `Shared`
    - primitives transverses admises
- `app`
  - `Interfaces`
  - `Adapters`
  - `Projections`
  - `Bootstrap`
- `tests`
  - `Domain`
  - `Application`
  - `Architecture`
  - `Integration`
  - `Contract`
  - `Feature`
  - `Performance`
  - `Security`
- `docs`
  - `architecture`
  - `implementation`
  - autres familles documentaires approuvées
- `tools`
  - `temporary`

Cette vue est une décision d’emplacement. Elle n’entraîne aucune création dans le présent sprint.

## 3.3 Zones standard du framework

Les emplacements standard nécessaires au fonctionnement de Laravel pourront exister à J0, mais ils restent techniques. Ils ne reçoivent aucun Aggregate, invariant, permission, transition ou décision SEO. Tout emplacement standard non nécessaire sera laissé inutilisé ou retiré selon le plan J0 validé.

---

# 4. Structure générale de l’application

## 4.1 Séparation officielle

L’application future est divisée en deux ensembles principaux :

- **`src` : cœur indépendant**, contenant les treize domaines, leurs Aggregates, Value Objects, politiques, cas d’usage et contrats ;
- **`app` : périphérie Laravel**, contenant la traduction des entrées, les adaptateurs techniques, les projections et l’amorçage.

## 4.2 Contenu futur de `src`

`src` peut contenir :

- concepts du Domaine ;
- Aggregate Roots, Entités et Value Objects ;
- événements métier ;
- politiques et services de domaine ;
- intentions et lectures conceptuelles ;
- cas d’usage indépendants du framework ;
- ports nécessaires aux cas d’usage ;
- contrats publics inter-modules ;
- erreurs métier ;
- primitives partagées explicitement admises.

`src` ne contient jamais :

- objet propre à Laravel ;
- mécanisme de routage ou requête web ;
- accès concret à PostgreSQL ;
- cache concret ;
- tâche de Queue concrète ;
- stockage média concret ;
- journal technique ;
- fichier de configuration ;
- dépendance vers `app`.

## 4.3 Contenu futur de `app`

`app` peut contenir :

- interfaces Laravel ;
- adaptateurs vers PostgreSQL et capacités externes ;
- assemblage des dépendances ;
- traductions entre formats externes et concepts du cœur ;
- projections reconstruisibles ;
- politiques techniques de reprise et d’observabilité ;
- composants d’amorçage indispensables.

`app` ne devient jamais propriétaire d’une règle métier. Une condition répétée dans la périphérie doit être replacée dans son domaine propriétaire.

---

# 5. Position des modules métier

## 5.1 Emplacement

Les modules seront placés sous `src/Modules`. Chaque domaine possède une enveloppe de premier niveau distincte.

## 5.2 Treize enveloppes officielles J0

| Domaine normatif | Identifiant de dossier futur | Statut J0 |
|---|---|---|
| Géographie | `Geography` | vide, premier domaine à ouvrir |
| Identité et accès | `IdentityAccess` | vide |
| Administration et audit | `AdministrationAudit` | vide |
| Professionnels | `Professionals` | vide |
| Catalogue immobilier | `RealEstateCatalog` | vide |
| Médias | `Media` | vide |
| Cycle de vie des annonces | `ListingLifecycle` | vide |
| Modération et signalements | `ModerationReports` | vide |
| Recherche et découverte | `SearchDiscovery` | vide |
| Contenus et SEO | `ContentSeo` | vide |
| Contacts et leads | `ContactsLeads` | vide |
| Migration Legacy | `LegacyMigration` | vide et temporaire |
| Monétisation et paiements | `MonetizationPayments` | vide et conditionnel |

Ces identifiants fixent les étiquettes physiques de J0. Ils ne fixent pas les espaces de noms PHP définitifs.

## 5.3 Structure interne type d’un module

Chaque enveloppe pourra recevoir, au moment de son ouverture, quatre sous-zones conceptuelles :

- `Domain` : règles, Aggregates, Entités, Value Objects, événements et politiques ;
- `Application` : cas d’usage et coordination locale ;
- `Contracts` : surface publique minimale du module ;
- `Read` : définitions de lectures appartenant au module, sans projection technique.

Une sous-zone n’est créée que lorsqu’elle reçoit une responsabilité réelle. Les enveloppes J0 ne sont pas remplies de fichiers ou de classes factices.

## 5.4 Interdiction d’Infrastructure dans le cœur

Une sous-zone générique `Infrastructure` n’est pas autorisée sous `src/Modules`. Les mécanismes Laravel, PostgreSQL, cache, média, paiement et messagerie appartiennent à `app/Adapters`, organisés par domaine et capacité. Cette décision rend la dépendance au framework physiquement visible.

---

# 6. Position des interfaces

## 6.1 Emplacement

Toutes les interfaces pilotées par Laravel seront placées sous `app/Interfaces`.

## 6.2 Familles futures

- `PublicWeb` : navigation publique et consultation ;
- `UserWeb` : parcours particuliers et professionnels authentifiés ;
- `Administration` : parcours internes ;
- `Console` : intentions opérées hors web ;
- `Scheduled` : déclencheurs planifiés ;
- autres interfaces uniquement après ADR ou besoin validé.

## 6.3 Règles

- une interface traduit une intention puis appelle un contrat applicatif ;
- elle ne décide ni état, ni permission finale, ni paiement, ni indexabilité ;
- public et administration utilisent les mêmes cas d’usage ;
- aucune interface ne lit directement la persistance d’un autre module ;
- une validation de format ne remplace pas une règle métier ;
- l’administration n’a aucun chemin privilégié vers les données.

## 6.4 Visibilité

Les interfaces peuvent dépendre des contrats publics des modules et des projections autorisées. Aucun module sous `src` ne dépend de `app/Interfaces`.

---

# 7. Position des adaptateurs

## 7.1 Emplacement

Les adaptateurs seront placés sous `app/Adapters`.

## 7.2 Organisation à deux axes

Ils seront classés d’abord par module propriétaire, puis par capacité technique, afin d’éviter un adaptateur universel de persistance ou de notification.

Exemples d’étiquettes de capacité futures :

- `Persistence` ;
- `Cache` ;
- `Search` ;
- `MediaStorage` ;
- `Notification` ;
- `Payment` ;
- `IdentityProvider` ;
- `Audit` ;
- `Clock` ou capacité temporelle lorsqu’un besoin externe existe.

Ces étiquettes ne créent ni classe ni package.

## 7.3 Règles

- un adaptateur satisfait un port défini par un besoin du cœur ;
- il ne publie pas sa technologie dans le contrat métier ;
- l’adaptateur PostgreSQL d’un module ne lit pas les structures internes d’un autre ;
- aucun adaptateur partagé n’écrit dans plusieurs domaines ;
- les transactions critiques restent orchestrées selon les frontières d’Aggregate ;
- un adaptateur externe possède comportement en panne, observabilité et tests de contrat ;
- le remplacement d’une capacité ne modifie pas le Domaine.

## 7.4 Adaptateurs transverses

Une capacité réellement transversale peut avoir un emplacement commun sous `app/Adapters/SharedCapabilities`, mais seulement si elle ne porte aucune règle de domaine et si chaque module conserve son contrat propre. Ce nom n’autorise pas une base d’accès universelle.

---

# 8. Position des projections de lecture

## 8.1 Emplacement

Les projections techniques seront placées sous `app/Projections`.

## 8.2 Familles officielles

- `Search` : résultats, facettes, compteurs et suggestions ;
- `Seo` : éligibilité, sitemap, maillage et représentations publiques ;
- `Administration` : files, supervision et vues internes autorisées ;
- `Statistics` : mesures et agrégations proportionnées ;
- `ProfessionalPortfolio` : portefeuille public dérivé si sa séparation est confirmée.

## 8.3 Règles

- toute projection est reconstruisible ;
- sa source est un fait métier ou une lecture publique approuvée ;
- elle ne modifie aucune Aggregate Root ;
- elle annonce sa fraîcheur ;
- une projection retardée ne peut autoriser une action critique ;
- suspension et retrait déclenchent une suppression prudente de la visibilité ;
- une facette de Recherche n’est pas automatiquement une page SEO ;
- une projection ne devient pas un contrat public du Domaine.

## 8.4 Définitions de lecture

Le besoin et le sens d’une lecture peuvent être définis dans `src/Modules/<Module>/Read`. Sa réalisation technique et son contenu dérivé se trouvent dans `app/Projections`. Cette séparation empêche PostgreSQL, un cache ou un moteur de recherche de définir le besoin métier.

---

# 9. Position des tests

## 9.1 Emplacement général

Toutes les preuves automatisées seront sous `tests`, hors des zones de production.

## 9.2 Familles

| Zone future | Objet | Dépendances autorisées |
|---|---|---|
| `tests/Domain` | Aggregates, Value Objects, politiques et invariants | `src` seulement |
| `tests/Application` | cas d’usage, permissions, ordre et erreurs | `src`, doubles contrôlés |
| `tests/Architecture` | frontières, cycles et dépendances interdites | structure et métadonnées de dépendance |
| `tests/Integration` | PostgreSQL et capacités réelles contrôlées | `src`, `app` et environnement de test |
| `tests/Contract` | ports, événements et capacités externes | contrats publics et adaptateurs concernés |
| `tests/Feature` | parcours Laravel complets | interfaces, cas d’usage et adaptateurs de test |
| `tests/Performance` | budgets, contention, charge et fraîcheur | environnement représentatif |
| `tests/Security` | autorisation, entrées hostiles, secrets et abus | surfaces autorisées |

## 9.3 Organisation par module

À l’intérieur de chaque famille, les tests sont regroupés par domaine. Une arborescence globale par type technique qui masquerait les propriétaires métier est interdite.

## 9.4 Interdictions

- pas de donnée personnelle réelle ;
- pas de test du Domaine dépendant de Laravel ou PostgreSQL ;
- pas de substitution permanente de PostgreSQL par une plateforme aux comportements différents dans les tests significatifs ;
- pas de pourcentage de couverture remplaçant les scénarios critiques ;
- pas de test d’une projection comme preuve de l’état source.

---

# 10. Position de la documentation

## 10.1 Emplacement

La documentation gouvernée reste sous `docs`.

## 10.2 Familles

- `docs/architecture` : Blueprints, Domain Mapping et Aggregate Boundaries ;
- `docs/implementation` : ADR techniques et plans de réalisation ;
- futures familles métier, exploitation, sécurité ou migration uniquement si leur besoin est validé ;
- `CHANGELOG.md` à la racine pour la chronologie documentaire globale.

## 10.3 Règles

- les documents normatifs ne sont pas dupliqués dans les modules ;
- un module peut référencer le document propriétaire, jamais maintenir une copie divergente ;
- secrets, données personnelles et informations d’accès sont interdits ;
- toute décision structurante possède un statut, une date et des références ;
- la documentation obsolète est marquée remplacée, pas supprimée sans trace ;
- les guides générés automatiquement restent distingués des décisions humaines.

---

# 11. Position des ADR

## 11.1 Emplacement officiel

Tous les ADR restent dans `docs/implementation` avec un identifiant séquentiel stable.

## 11.2 Nommage

Le format documentaire reste `ADR-NNNN-SUJET.md`, avec sujet court, explicite et en majuscules séparées par des tirets.

## 11.3 Cycle de vie

- Proposé ;
- Accepté ;
- Rejeté ;
- Remplacé par un autre ADR ;
- Obsolète lorsque son contexte disparaît sans nouvelle décision.

## 11.4 Références locales

Un module ne possède pas son propre répertoire d’ADR. Les décisions restent centralisées pour permettre la revue des dépendances transverses. Une note locale non approuvée ne peut contredire un ADR.

---

# 12. Position des scripts temporaires

## 12.1 Emplacement

Les futurs outils temporaires, lorsqu’ils seront autorisés, seront placés sous `tools/temporary`.

## 12.2 Sous-zones candidates

- `legacy-migration` pour les opérations temporaires de reprise ;
- `diagnostics` pour une investigation bornée ;
- `one-off` pour une opération exceptionnelle explicitement approuvée.

Ces sous-zones ne seront créées que lorsqu’un besoin existe.

## 12.3 Conditions obligatoires

Chaque outil temporaire futur possède :

- un propriétaire ;
- une finalité ;
- un ticket ou une décision ;
- une date d’expiration ;
- des entrées et sorties documentées ;
- une protection contre l’usage en production non autorisé ;
- des journaux proportionnés ;
- un test ou une vérification adaptée ;
- une procédure de suppression.

## 12.4 Interdictions

- aucune dépendance du runtime courant vers `tools` ;
- aucun secret incorporé ;
- aucune correction manuelle silencieuse ;
- aucun outil temporaire devenu tâche permanente ;
- aucune règle métier uniquement présente dans un outil ;
- aucun outil Legacy dans `src/Shared`, `app/Bootstrap` ou un module courant.

---

# 13. Position de Migration Legacy

## 13.1 Double isolement

Migration Legacy possède deux emplacements futurs complémentaires :

- `src/Modules/LegacyMigration` pour Lot Legacy, Dossier de décision Legacy, Rapprochement de reprise, cas d’usage et contrats temporaires ;
- `tools/temporary/legacy-migration` pour les outils ponctuels autorisés.

Les adaptateurs temporaires nécessaires seront sous `app/Adapters/LegacyMigration` et resteront supprimables.

## 13.2 Direction de dépendance

Migration Legacy peut dépendre des contrats publics des domaines cibles pour soumettre des candidats. Aucun domaine courant ne dépend de LegacyMigration, de ses adaptateurs ou de ses outils.

## 13.3 Interdictions

- aucune référence au Legacy dans les Aggregates courants ;
- aucun type historique dans `src/Shared` ;
- aucune lecture directe du Legacy par le produit public ou administratif courant ;
- aucune donnée candidate acceptée sans validation du propriétaire cible ;
- aucune conservation du module après ses critères de clôture.

## 13.4 Suppression

La suppression de Migration Legacy est une propriété de la structure : ses emplacements sont distincts et aucune dépendance entrante n’est autorisée. Après validation finale, expiration du retour et zéro écart inexpliqué, l’enveloppe, ses adaptateurs et ses outils pourront être retirés ensemble, tandis que les preuves historiques justifiées seront transférées sous l’autorité d’audit prévue.

---

# 14. Modules interdits dans le socle partagé

## 14.1 Emplacement partagé

Le seul emplacement partagé du cœur est `src/Shared`.

## 14.2 Concepts explicitement interdits

Ne peuvent jamais entrer dans `src/Shared` :

- Compte, rôle, consentement ou Mandat ;
- Professionnel ou établissement ;
- Annonce, bien, catégorie, intention ou prix ;
- état d’annonce ou transition ;
- Galerie, Média ou conformité média ;
- Ville, Quartier, Lieu ou alias géographique ;
- résultat, facette ou pertinence ;
- Lead ou contact ;
- Signalement, preuve ou décision de modération ;
- Page, Guide, URL, redirection ou décision d’indexation ;
- Offre, Commande, Paiement ou remboursement ;
- approbation, audit métier ou paramètre gouverné ;
- Lot, candidat ou correspondance Legacy.

## 14.3 Contenu admissible

Peuvent être candidats, après revue :

- identité générique typée sans connaissance du domaine ;
- abstraction du temps ;
- corrélation ;
- résultat générique d’une opération ;
- primitives de pagination sans règle de Recherche ;
- événement de base sans contenu métier ;
- erreurs techniques neutres strictement nécessaires au cœur.

## 14.4 Test d’admission

Un élément partagé doit :

1. être utilisé sans adaptation par au moins deux domaines ;
2. ne contenir aucune condition métier ;
3. rester stable si un domaine change ;
4. ne pas créer de cycle ;
5. avoir un propriétaire de gouvernance ;
6. pouvoir être retiré d’un domaine sans changer son langage.

En cas de doute, l’élément reste dans son domaine d’origine.

---

# 15. Règles de dépendances physiques

## 15.1 Directions autorisées

| Source | Cible autorisée | Condition |
|---|---|---|
| `src/Modules/<Module>/Domain` | lui-même et `src/Shared` | partagé admis, aucune règle externe |
| `src/Modules/<Module>/Application` | Domain local, contrats publics externes et Shared | coordination explicite seulement |
| `src/Modules/<Module>/Contracts` | concepts publics minimaux du module et Shared | aucune structure interne exposée |
| `src/Modules/<Module>/Read` | contrats de lecture et Value Objects publics nécessaires | aucune technologie de projection |
| `app/Interfaces` | contrats applicatifs et projections autorisées | aucune mutation directe de données |
| `app/Adapters` | ports du cœur et mécanismes techniques | dépendance vers le cœur uniquement |
| `app/Projections` | événements publics et définitions de lecture | aucune autorité de décision |
| `app/Bootstrap` | contrats d’assemblage et périphérie | aucune règle métier |
| `tests` | zones correspondant au niveau testé | dépendances contrôlées par famille |
| `tools/temporary` | contrats temporaires explicitement autorisés | aucune dépendance entrante du runtime |

## 15.2 Directions interdites

- `src` vers `app` ;
- Domain vers Application, Contracts techniques ou Read ;
- un module vers l’intérieur d’un autre module ;
- un domaine courant vers LegacyMigration ;
- une projection vers un adaptateur d’écriture métier ;
- une interface vers PostgreSQL ou un stockage concret ;
- Shared vers un module ;
- production vers `tests` ou `tools` ;
- adaptateur d’un module vers les données privées d’un autre ;
- cycle, direct ou transitif, entre modules.

## 15.3 Interactions inter-modules

Elles passent uniquement par :

- une intention publique adressée au propriétaire ;
- une lecture publique approuvée ;
- un événement métier publié ;
- une référence d’identité stable.

La proximité physique n’accorde aucun accès supplémentaire.

---

# 16. Règles de visibilité

## 16.1 Privé par défaut

Tout élément d’un module est privé sauf présence volontaire dans `Contracts` ou exposition documentaire d’un événement public.

## 16.2 Surface publique

La surface publique d’un module peut contenir :

- intentions autorisées ;
- résultats de cas d’usage ;
- identités publiques ;
- événements métier stables ;
- lectures nécessaires ;
- erreurs métier que le consommateur doit traiter.

Elle ne contient pas :

- Aggregate interne modifiable ;
- Entité interne ;
- mécanisme de persistance ;
- objet Laravel ;
- structure PostgreSQL ;
- secret ou configuration ;
- projection technique complète.

## 16.3 Visibilité par acteur

La visibilité physique d’un contrat ne constitue pas une permission. Les rôles et règles de PERMISSIONS-MATRIX s’évaluent à chaque action et ressource.

## 16.4 Contrôle

J0 devra inclure un contrôle automatique capable d’échouer si une dépendance interdite, un accès interne ou un concept Laravel apparaît dans le Domaine.

---

# 17. Politique des espaces de noms

## 17.1 Décision de principe

Les espaces de noms refléteront la séparation entre cœur, modules et périphérie. Ils ne seront toutefois fixés définitivement que juste avant J0, après validation du nom racine et du mécanisme de chargement.

## 17.2 Propriétés obligatoires

- un préfixe distinct pour le cœur sous `src` ;
- un segment visible par domaine ;
- un segment distinct pour Domain, Application, Contracts et Read ;
- un préfixe de périphérie Laravel distinct sous `app` ;
- aucune ambiguïté entre module métier et adaptateur ;
- aucun espace de noms Legacy partagé avec le produit courant ;
- correspondance prévisible entre emplacement et espace de noms ;
- absence d’alias masquant la vraie propriété.

## 17.3 Éléments ouverts

Restent à décider : préfixe racine exact, casse, traduction française ou anglaise de certains concepts, mapping de chargement et convention pour les tests.

## 17.4 Interdiction

Le préfixe Laravel ou un nom de package ne doit pas devenir la racine conceptuelle du Domaine. Un espace de noms générique tel que `Core`, `Common`, `Services` ou `Models` ne peut contenir plusieurs domaines.

---

# 18. Politique de nommage

## 18.1 Dossiers

- anglais technique cohérent pour les identifiants physiques de premier niveau ;
- noms métier issus du langage partagé pour les concepts ;
- noms au singulier ou pluriel selon une convention décidée avant J0 et appliquée uniformément ;
- aucun acronyme non normatif ;
- aucune référence à une technologie dans un nom de Domaine ;
- suffixe `Legacy` uniquement dans la zone temporaire approuvée.

## 18.2 Intentions et événements

- intentions formulées par un verbe précis au présent ou à l’infinitif selon la convention retenue ;
- événements formulés comme faits accomplis ;
- lectures nommées selon le résultat métier ;
- erreurs nommées selon l’invariant violé ;
- adaptateurs nommés selon le port satisfait et la technologie périphérique.

## 18.3 Termes refusés

Les noms vagues suivants sont interdits sans qualification :

- `Core` ;
- `Common` ;
- `Utils` ;
- `Helpers` ;
- `Managers` ;
- `Services` global ;
- `Models` global ;
- `Data` global ;
- `Misc` ;
- `New` ou `V2` ;
- nom de concept Legacy pour un concept courant.

## 18.4 Cohérence documentaire

Tout nom physique doit être traçable vers un domaine, Aggregate, capacité ou décision. Une traduction qui change le sens normatif doit être refusée.

---

# 19. Politique d’évolution de la structure

## 19.1 Changements ordinaires

Créer une sous-zone déjà prévue dans un module ouvert est autorisé si elle reçoit immédiatement une responsabilité réelle et respecte les contrôles.

## 19.2 Changements exigeant un ADR

- nouveau domaine ;
- fusion ou division d’un module ;
- déplacement d’un Aggregate Root ;
- création d’une nouvelle zone de premier niveau ;
- modification de la direction des dépendances ;
- introduction d’un espace partagé supplémentaire ;
- déplacement d’une règle vers `app` ;
- extraction opérationnelle d’un module ;
- prolongation de Migration Legacy ;
- changement du mapping des espaces de noms ;
- exception durable à un contrôle architectural.

## 19.3 Refactoring physique

Un déplacement doit :

1. préserver le propriétaire métier ;
2. conserver les contrats publics ou organiser leur transition ;
3. ne pas mélanger une évolution métier majeure ;
4. mettre à jour les contrôles et tests ;
5. documenter le retour ;
6. supprimer les anciens chemins après la période convenue ;
7. éviter les alias permanents.

## 19.4 Revue périodique

La structure sera revue après les trois premiers domaines, avant Recherche/SEO, avant Migration Legacy et avant toute extraction. La revue vérifie dépendances, taille du Shared, surfaces publiques, duplication et dérive de la périphérie.

## 19.5 Règle de stabilité

La structure n’est pas immuable, mais aucune évolution n’est motivée par une préférence esthétique seule. Elle doit réduire un risque ou protéger une frontière démontrée.

---

# 20. Critères d’acceptation

L’ADR est accepté si :

- les zones `src`, `app`, `tests`, `docs` et `tools` ont une responsabilité non ambiguë ;
- le Domaine sous `src` ne dépend pas de Laravel, PostgreSQL ou d’une capacité externe ;
- Laravel reste physiquement dans la périphérie `app` et ses zones standard ;
- les treize domaines sont visibles sous `src/Modules` ;
- leurs identifiants J0 et leur état vide sont explicités ;
- interfaces, adaptateurs et projections sont séparés ;
- aucun adaptateur concret n’est placé dans le cœur ;
- les lectures de Recherche, SEO, administration et statistiques restent reconstruisibles ;
- les familles de tests reflètent les niveaux de preuve ;
- les ADR sont centralisés sous `docs/implementation` ;
- les outils temporaires sont hors du runtime et possèdent une date de retrait ;
- Migration Legacy est doublement isolée et supprimable ;
- aucun domaine normatif ne peut entrer dans `src/Shared` ;
- les dépendances autorisées et interdites sont vérifiables ;
- tout élément est privé par défaut ;
- les contrats publics restent minimaux et sans technologie ;
- les espaces de noms restent à finaliser tout en respectant les propriétés obligatoires ;
- les noms génériques et le Core universel sont refusés ;
- toute évolution structurelle majeure exige un ADR ;
- aucun choix métier validé n’est modifié ;
- aucune structure réelle n’est créée par ce document.

---

# 21. Questions ouvertes

## Bloquantes avant J0

1. Quel préfixe racine exact sera retenu pour les espaces de noms du cœur ?
2. Quel préfixe distinguera la périphérie Laravel ?
3. Quelle convention de casse et de singulier/pluriel s’appliquera aux dossiers et espaces de noms ?
4. Les identifiants physiques anglais des treize domaines sont-ils tous validés par les propriétaires métier ?
5. Quel mécanisme de chargement associera `src` à son espace de noms sans coupler le Domaine ?
6. Quel outil vérifiera les dépendances physiques et les cycles ?
7. Quelles zones standard Laravel seront conservées, vidées ou laissées inutilisées à J0 ?
8. Comment matérialiser les enveloppes vides sans créer de classes ou fichiers factices ?
9. Le premier commit doit-il créer toutes les sous-zones internes ou seulement les treize enveloppes ?
10. Quelle convention de tests associera une preuve à son domaine et à son niveau ?

## Avant les domaines concernés

11. `Read` est-il nécessaire dans chaque module ou seulement lorsqu’une lecture publique existe ?
12. Le portefeuille professionnel justifie-t-il une famille de projection distincte ?
13. Comment nommer les contrats inter-modules sans exposer les Aggregates internes ?
14. Quelle convention identifie un événement public et son évolution ?
15. Quels adaptateurs peuvent réellement être transverses sans devenir universels ?
16. Quelle zone porte la publication atomique future des événements sans contaminer le Domaine ?
17. Les interfaces publique et utilisateur resteront-elles séparées physiquement dès J0 ?
18. Quelle structure d’adaptateur PostgreSQL garantit l’absence d’accès inter-module ?

## Gouvernance et fin de vie

19. Qui approuve l’entrée d’un élément dans `src/Shared` ?
20. Quel seuil de duplication justifie une proposition de partage ?
21. Qui possède les contrôles architecturaux et traite leurs exceptions ?
22. Quel mécanisme bloque une dépendance entrante vers LegacyMigration ?
23. Où seront transférées les preuves historiques justifiées après suppression de Migration Legacy ?
24. Quels critères imposent une nouvelle zone de premier niveau ?

---

# Synthèse de la structure retenue

La structure future place les treize domaines dans `src/Modules`, le partagé minimal dans `src/Shared`, les interfaces et adaptateurs Laravel dans `app`, les projections dans `app/Projections`, les preuves sous `tests`, les décisions sous `docs` et les outils temporaires sous `tools/temporary`.

Chaque module disposera, seulement lorsqu’il sera ouvert, de zones Domain, Application, Contracts et Read. Aucun adaptateur concret ni concept Laravel ne sera admis dans `src`. Les espaces de noms définitifs restent à décider avant J0.

# Protection des frontières métier

La séparation physique rend la direction des dépendances visible : Laravel et PostgreSQL dépendent des besoins du cœur, jamais l’inverse. Les contrats publics empêchent les accès internes inter-modules. Les projections sont isolées des décisions. `src/Shared` refuse tous les concepts des treize domaines. Migration Legacy peut être supprimée sans casser le produit courant.

# Décisions restant ouvertes

Restent à fixer : préfixes d’espaces de noms, conventions de casse, mapping de chargement, outil de contrôle architectural, zones Laravel conservées à J0, profondeur initiale des enveloppes, conventions de tests, publication technique des événements et règles précises d’admission dans Shared.

# Confirmation de périmètre

Ce livrable décrit uniquement une organisation future. Aucun projet Laravel, dossier applicatif, espace de noms définitif, code, fichier de configuration, migration ou package n’a été créé.
