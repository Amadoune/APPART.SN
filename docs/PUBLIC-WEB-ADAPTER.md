# Public Web Adapter

## Rôle

Le Public Web Adapter définit et certifie en isolation la première frontière HTTP d'annonce publique à partir du seul modèle autorisé : `PublicListingReadModel`. Il n'est pas encore exploitable en production faute d'implémentation et de binding Laravel pour `PublicListingQuery`.

```text
HTTP
  ↓
PublicListingController
  ↓
PublicListingQuery
  ↓
PublicListingReadModel
  ↓
Blade public-listing
```

Aucune autre dépendance applicative ou métier n'intervient dans cette chaîne.

## Route

`GET /{canonicalPath}` accepte la forme existante `annonces/{canonical}`. Le paramètre est transmis tel quel au query. Un `ListingId` isolé ne correspond pas à la route. La route ne sait pas déterminer si un chemin syntaxiquement valide appartient à l'historique : le query doit résoudre uniquement le canonical courant et retourner `null` pour un ancien canonical. Aucun fallback n'existe.

## Réponses

- query lié et modèle absent : HTTP 404 ;
- query lié et read model présent : HTTP 200 et vue `public-listing` ;
- aucune redirection ou reconstruction implicite.

Ces deux comportements sont certifiés avec le harness en mémoire sous `tests/`. Sans ce harness, le conteneur ne peut pas instancier `PublicListingQuery` et la requête réelle retourne HTTP 500.

## Rendu

La vue affiche les données publiques déjà composées : contenu, média, caractéristiques et breadcrumb. Elle consomme directement `canonicalUrl`, `htmlRobotsDirective` et `publicJsonLd`. Une décision noindex conserve sa directive finale et n'émet pas de bloc JSON-LD lorsque celui-ci est absent du read model.

## Dépendances autorisées

Le Controller injecte uniquement `PublicListingQuery`. Laravel fournit les mécanismes HTTP et View. Le read model est transmis à Blade par identité, sans copie ni transformation.

## Exclusions

L'adaptateur ne consulte aucun Aggregate, Repository, PDO, SQL, Builder, Policy, Catalog, projection Search ou SEO. Il ne contient aucune règle métier, Search ou SEO. Aucun Repository, migration, table, Dispatcher, Outbox, Queue, Listener ou Unit of Work n'est introduit.

## Exploitation future

Une implémentation de production de `PublicListingQuery` devra être fournie par un sprint d'infrastructure explicitement autorisé. Elle devra respecter le contrat existant sans modifier ce Controller ni la vue.

Avant sa création, cette implémentation devra faire l'objet d'une décision explicite couvrant son ownership, ses sources de données, sa stratégie de reconstruction, son éventuel besoin de persistance, ses garanties de fraîcheur et son impact sur les quatre repositories PostgreSQL certifiés.

## Certification corrigée

**GO avec réserve — Public Web Adapter Contract/Test Foundation.**

Le Controller, la route, Blade et leurs garde-fous sont approuvés. L'adaptateur est certifié en isolation, non encore exploitable en production. La réserve sera levée uniquement lorsqu'une requête HTTP utilisant le bootstrap réel pourra résoudre `PublicListingQuery` et servir correctement 200 ou 404.
