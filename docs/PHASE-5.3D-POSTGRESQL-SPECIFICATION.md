# Phase 5.3D — PostgreSQL Specification

## Migration

`063_moderation_reports.sql`

Rollback :

`063_moderation_reports.down.sql`

## Schéma owner

`moderation_reports`

## Tables

| Table | Nature |
|---|---|
| `cases` | snapshot racine versionné |
| `report_revisions` | historique append-only |
| `finding_revisions` | historique append-only |
| `decision_revisions` | historique append-only |
| `decision_supersessions` | relations de supersession permanentes |
| `case_intents` | journal d'idempotence permanent |
| `queue_items` | projection et lease propriétaire |
| `queue_checkpoints` | checkpoint monotone |

## Contraintes

- UUID pour les identités ;
- versions strictement positives ;
- checkpoints positifs ou nuls ;
- checksum hexadécimal de 64 caractères ;
- payloads JSONB de type `object` ;
- statuts et target types fermés ;
- cohérence locale des champs de lease ;
- aucune FK cross-domain ;
- aucune FK locale avec cascade ;
- aucun trigger ;
- aucune lecture ou écriture d'un autre schéma.

## Index

- cible de dossier ;
- dernière révision par enfant ;
- claim de queue par état/priorité/date ;
- reprise des leases expirées.

## Atomicité

La sauvegarde d'un dossier groupe :

1. advisory lock ;
2. contrôle d'intent ;
3. contrôle de version ;
4. snapshot racine ;
5. révisions append-only ;
6. supersessions ;
7. journal d'intent ;
8. commit local ou rollback complet.

## Rollback

Le down supprime les tables dans l'ordre inverse puis le schéma. Il ne touche
aucune migration 001–062 ni aucun schéma externe.
