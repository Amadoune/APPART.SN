# MediaCollection Registry Contract Foundation

## Statut

Fondation contractuelle du Sprint 2.7, exécutée uniquement contre `FakeMediaCollectionRegistry`. Elle prépare une éventuelle tranche PostgreSQL Media sans Repository, mapper, snapshot, transaction, SQL ou migration.

## Inventaire

- Aggregate Root : `MediaCollection`.
- Entité interne immuable : `MediaItem`.
- Identité du Root : `MediaCollectionId`, permanente.
- Identité enfant : `MediaId`, réservée globalement et définitivement par le port spécialisé.
- Référence externe : `PropertyId`, sans réservation de propriété.
- Value Objects : `MediaType`, `MediaChecksum`, `MediaOrder`, `MediaCaption`, `MediaSource`, `MediaStatus`.
- État du Root : aucun enum terminal global; une collection reste mutable tant qu’une opération valide existe.
- États des items : Active, Removed et Archived; Removed et Archived sont terminaux.
- Reconstruction officielle : `MediaCollection::reconstitute`.
- Version : entière non négative, initialement zéro, incrémentée exclusivement par le Domain.
- Date : `lastChangedAt`, désormais observable en lecture seule et contrôlant la chronologie.
- Événements : MediaAdded, MediaRemoved, MediaArchived, MediaReordered, MediaMarkedPrimary, MediaCaptionChanged.

## Invariants

Chaque MediaId est unique dans le Root et réservé définitivement au niveau Registry. Le checksum est unique sur tout l’historique de la collection. L’ordre est unique parmi les items actifs. Le premier média devient primaire; un seul item actif peut l’être. Retirer ou archiver le primaire exige un remplacement actif explicite. Reorder doit contenir exactement tous les actifs, sans doublon, et changer effectivement l’ordre. Les items Removed/Archived ne redeviennent jamais actifs et ne sont plus modifiables.

## Profil contractuel

| Axe | Garantie |
|---|---|
| Lecture | `find` retourne null ou une collection détachée complète. |
| Add | Réserve uniquement MediaCollectionId et ajoute atomiquement une collection, normalement vide. |
| Save | Sauvegarde conditionnelle par `expectedVersion` pour une mutation sans nouvelle réservation. |
| Réservation | `saveWithMediaReservation` réserve MediaId et sauvegarde la mutation dans la même atomicité. |
| Concurrence | Root absent ou version périmée → `ConcurrentMediaCollectionModification`. |
| Conflits | MediaCollectionIdConflict et MediaIdConflict sont distincts. |
| Rollback | Échec spécialisé : ni mutation ni MediaId visibles; événements appelants conservés. |
| Reconstruction | Root, date, version, items, ordre, primaire, options et états fidèles, sans événements. |
| Détachement | Deux lectures et le stockage interne ne partagent aucun état mutable. |
| Terminalité | Removed et Archived restent terminaux après rechargement. |

## Scénarios partagés

Les 14 scénarios couvrent absence, collection vide, fidélité peuplée, détachement, indépendance, événements résiduels/rejoués, conservation des événements, conflit de Root, save/version, stale/absent, réservation MediaId globale, permanence après archivage, rollback du save simple, rollback atomique de mutation/réservation, reconstruction complète des items et isolation des scénarios.

La classe d’entrée Fake ne redéfinit aucun scénario. Un futur backend remplacera uniquement le harness.

## Règles non applicables

- Aucune business key globale : checksum et ordre sont locaux.
- Aucun état terminal du Root lui-même.
- Aucun historique d’événements persisté par le port.
- Aucun delete de collection ni libération de MediaId.
- Aucune idempotence métier.
- Le Fake n’expose pas d’échec injecté sur `add`; l’atomicité du conflit de Root est vérifiée sans écrasement.

## Décision

La fondation est directement réutilisable par un futur backend PostgreSQL sans nouvelle modification Domain. Elle n’autorise encore aucune persistance Media ni aucun quatrième Repository.
