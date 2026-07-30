# Phase 4.9P — Historical Account Persistence Foundation Final Certification

## Périmètre audité

```text
4.9P-A Discovery
→ 4.9P-B-R1 Secure Snapshot Boundary
→ 4.9P-B Contract and Mapping
→ 4.9P-C PostgreSQL Persistence
→ 4.9P-D Runtime Composition
```

Tous les jalons A à D sont GO CERTIFIÉ et fermés. Cette certification finale
consolide et gèle l'existant; elle n'introduit aucune implémentation.

## Matrice GO FINAL

| Critère | Preuve | Verdict |
|---|---|---|
| `AccountRegistry` intégralement opérationnel | `find`, `add`, `save` certifiés | SATISFAIT |
| Snapshot sécurisé complet | Snapshot V1 racine, Credential, Verification, RoleAssignment, Consent | SATISFAIT |
| Reconstruction fidèle | round-trip complet sans mutation ni événement artificiel | SATISFAIT |
| Secrets protégés | opaque, non sérialisable, non JSON, debug redacted, usage borné | SATISFAIT |
| Repository atomique | racine et enfants dans la même transaction | SATISFAIT |
| Transaction externe respectée | join sans commit ni rollback de l'appelant | SATISFAIT |
| Concurrence optimiste | UPDATE versionné; un succès sous concurrence | SATISFAIT |
| Corruption distincte de l'absence | corruption durable fermée, `null` réservé à Missing | SATISFAIT |
| Migration additive et réversible | cinq tables 042; rollback inverse | SATISFAIT |
| Journal 041 inchangé | aucune lecture, écriture ou migration croisée | SATISFAIT |
| Runtime unique et paresseux | singleton mapper/repository, alias port unique | SATISFAIT |
| Bootstrap sans effet | aucune résolution, requête ou transaction | SATISFAIT |
| Runtime Health non muté | Healthy — 55 capacités | SATISFAIT |
| Frontière Account Status préservée | versions et responsabilités séparées | SATISFAIT |
| Baselines consolidées | matrice 4.9P-B/C/D verte | SATISFAIT |
| Risques résiduels enregistrés | registre HAR-01 à HAR-07 | SATISFAIT |
| Aucun jalon futur anticipé | recertification 4.9C fermée; 4.9D suspendu | SATISFAIT |

## Garanties certifiées

La fondation constitue une source de production complète, durable et
résoluble pour l'agrégat historique Account. Elle conserve fidèlement
Credential, les deux Verification et les historiques ordonnés RoleAssignment
et Consent. Elle protège la frontière des secrets et applique les invariants
PostgreSQL, transactionnels et de concurrence documentés.

Elle n'est pas le lifecycle Account Status. L'existence et la version
historique de l'Account restent séparées du statut courant et de la version du
journal 041.

## Inventaires constitutifs

- `HISTORICAL-ACCOUNT-FROZEN-CONTRACT-INVENTORY.md`;
- `HISTORICAL-ACCOUNT-MIGRATION-INVENTORY.md`;
- `HISTORICAL-ACCOUNT-BASELINE-MATRIX.md`;
- `HISTORICAL-ACCOUNT-RESIDUAL-RISK-REGISTER.md`;
- `HISTORICAL-ACCOUNT-BOUNDARY-MATRIX.md`.

## Verdict certifié

```text
4.9P — Historical Account Persistence Foundation
→ GO FINAL
→ FERMÉE
→ GELÉE

Recertification ciblée 4.9C
→ OUVERTE

4.9D — Account Status Runtime Composition
→ RESTE SUSPENDU
```

Après le GO FINAL, toute modification des contrats, Snapshot V1,
Repository, migration 042 ou bindings certifiés exigera un amendement
versionné, une analyse d'impact et une nouvelle certification.
