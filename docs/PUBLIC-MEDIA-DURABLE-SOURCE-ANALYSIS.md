# Sprint 3.8E — Analyse Public Media Durable Source

## Décision

La décision publique Media est persistée dans une tranche PostgreSQL dédiée, sans lire ni modifier l'Aggregate `MediaCollection`. Elle contient l'identité de collection, la révision stable 3.7B, la couverture déjà choisie, la galerie dans son ordre déjà décidé, les URLs retenues et leurs variantes publiques déjà établies.

Le modèle applicatif conserve l'ordre reçu. Il ne choisit aucune couverture, ne filtre aucun média, ne trie aucune galerie et ne fabrique aucune variante.

## Séparation

- `PublicMediaDecision` certifie la cohérence entre la révision et le payload public canonique.
- `PublicMediaDecisionReader` et `PublicMediaDecisionWriter` portent les contrats applicatifs spécialisés.
- `PostgreSqlPublicMediaMapper` réalise uniquement la conversion explicite.
- le reader relit et valide ; le writer applique une transition monotone.
- la table `public_media.decisions` est propriétaire de la persistance technique.

## Atomicité

Une seule ligne contient révision et contenu. La contrainte `revision_checksum = payload_checksum` interdit leur désalignement. L'écriture participe à une transaction externe ou ouvre une transaction locale. Un rollback supprime donc simultanément révision et contenu.

## Concurrence

Le writer prend un verrou transactionnel déterministe par `media_collection_id`, puis verrouille la ligne existante. Deux écritures identiques convergent vers `Applied` et `AlreadyApplied`; une seule ligne durable subsiste.

## Périmètre

Aucun Domain Media, Aggregate, Store, Updater, composant Delivery, Runtime, Laravel ou HTTP n'est modifié. Cette tranche n'est pas le futur `PublicListingProjectionSource`.
