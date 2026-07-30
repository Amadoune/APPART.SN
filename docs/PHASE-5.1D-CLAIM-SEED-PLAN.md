# Phase 5.1D — Identity Claim Seed Plan

## Import

Chaque Snapshot V1 complet produit exactement :

- un User Profile version 1 ;
- un claim Email ;
- un claim Phone ;
- une ligne de manifest liant les trois écritures au run.

Les claims courants sont créés `Active`. La contrainte permanente unique
`(claim_type, claim_fingerprint)` de 048 conserve la réservation même après un
futur état `Superseded`, `Released` ou `Expired` : une ancienne identité ne
devient jamais réattribuable en V1.

## Normalisation

Version : `iam-profile-v1`.

| Valeur | Règle |
|---|---|
| nom | trim et espaces internes réduits |
| email | trim et lowercase UTF-8 |
| téléphone | préfixe `+` conservé, séparateurs supprimés |

Le fingerprint est calculé après normalisation par un protecteur injecté. Le
Seed n'embarque aucune clé et ne journalise aucune PII.

## Idempotence et concurrence

- IDs Profile/Claims/Intent déterministes ;
- checksum de run indépendant de l'ordre d'entrée ;
- verrouillage et optimistic locking hérités des stores 5.1C ;
- unicité atomique PostgreSQL des claims ;
- un seul registre `ProfileClaims` peut passer à `Profile`.

Un conflit ne devient jamais un succès implicite. Il entraîne rollback de la
transaction et résultat fermé `PersistenceRejected`.
