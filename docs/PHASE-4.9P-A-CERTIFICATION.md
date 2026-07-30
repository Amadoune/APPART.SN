# Phase 4.9P-A — Certification

## Livrables

- `PHASE-4.9P-A-HISTORICAL-ACCOUNT-PERSISTENCE-DISCOVERY.md`;
- `HISTORICAL-ACCOUNT-AGGREGATE-MAPPING-INVENTORY.md`;
- `HISTORICAL-ACCOUNT-DATA-OWNERSHIP-MATRIX.md`;
- `HISTORICAL-ACCOUNT-PERSISTENCE-RISK-MATRIX.md`;
- `HISTORICAL-ACCOUNT-TRANSACTION-CONCURRENCY-BLUEPRINT.md`;
- roadmap 4.9 synchronisée.

## Matrice GO

| Critère | Résultat |
|---|---|
| agrégat entièrement inventorié | SATISFAIT |
| données de reconstitution identifiées | SATISFAIT |
| owner de chaque donnée | SATISFAIT |
| rôles, Credential, Verification, Consent clarifiés | SATISFAIT |
| stratégie complète possible | SATISFAIT, sous gate snapshot P-B |
| concurrence historique attribuée | SATISFAIT |
| transaction définie | SATISFAIT |
| source legacy ou création initiale identifiée | SATISFAIT par création native `RegisterAccount → add` |
| aucune décision métier déplacée | SATISFAIT |
| aucune implémentation | SATISFAIT |
| compatibilité 4.9C-R1 / journal 041 | SATISFAIT |

## Réserve normative

Le domaine ne permet pas encore l'extraction et l'hydratation complètes de
plusieurs valeurs privées. Cette réserve ne rend pas la stratégie impossible,
mais crée un gate obligatoire au début de 4.9P-B :

```text
Secure Persistence Snapshot Boundary
→ contrat versionné
→ aucun setter
→ aucune réflexion
→ aucune sérialisation générique
→ aucun événement artificiel
```

## Provenance legacy

Aucune base legacy, colonne, dump ou configuration de source n'est disponible.
Aucune migration de données n'est autorisable. La création initiale native est
la seule stratégie certifiable à ce stade.

## Validation

Le sprint est documentaire. Aucun test, aucune migration et aucune campagne
PostgreSQL ne sont revendiqués.

## Verdict certifié

```text
4.9P-A
→ GO CERTIFIÉ
→ FERMÉ

4.9P-B
→ OUVERT

4.9P-B-R1
→ GATE OBLIGATOIRE

4.9D
→ RESTE SUSPENDU
```

Le GO autorise la frontière contractuelle et le mapper pur de 4.9P-B. Il
n'autorise ni Repository, ni migration, ni SQL, ni Runtime.
