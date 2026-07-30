# Property Lifecycle Workflow Analysis

## Séparation des capacités

Le cycle de vie d'un Property décrit l'exploitabilité du bien immobilier. Il ne décrit ni la publication d'une annonce, ni sa visibilité publique. Plusieurs Listings pourront ultérieurement référencer un même Property sans transférer leurs états de publication au bien.

Le `PropertyStatus` historique du domaine certifié reste inchangé. La nouvelle machine d'état est un contrat applicatif autonome et n'est reliée à aucune persistance dans 4.2A.

## États retenus

- `Draft` : bien préparé mais pas encore exploitable ;
- `Active` : bien exploitable ;
- `UnderMaintenance` : indisponibilité planifiée avec retour attendu ;
- `Unavailable` : indisponibilité constatée, distincte d'une maintenance planifiée ;
- `Decommissioned` : retrait définitif de l'exploitation, conservé avant archivage ;
- `Archived` : état terminal de conservation.

Les états de revue ou de publication sont exclus : ils appartiennent à Listing Publication.

## Décision

Le workflow accepte uniquement un état et une action fermés. Il retourne `Allowed` avec une transition complète, ou `Denied` avec un diagnostic typé. Il n'utilise aucune identité, horloge ou dépendance externe.
