# Phase 4.9C-R1 — Historical Coexistence Gate Certification

## Livrables

- `PHASE-4.9C-R1-ACCOUNT-STATUS-HISTORICAL-COEXISTENCE-GATE.md`;
- `ACCOUNT-STATUS-HISTORICAL-AUTHORITY-MATRIX.md`;
- `ACCOUNT-STATUS-HISTORICAL-BOOTSTRAP-CUTOVER.md`;
- mise à jour de `PHASE-4.9-ROADMAP.md`.

## Réponses normatives

| Question | Décision |
|---|---|
| source du statut courant | futur journal Account Status |
| version de concurrence | version lifecycle dédiée |
| `AccountRegistry` unique port durable | non; port historique inchangé et borné |
| rôle du journal | source d'autorité, pas projection |
| double écriture | interdite; journal lifecycle uniquement |
| comptes sans contexte | `LegacyUninitialized`, amorçage déterministe en version 0 |
| absence de contexte / compte | résultats distincts |
| double décision | mutations historiques interdites au chemin 4.9 |

## Critères satisfaits par conception

- une seule autorité durable est désignée;
- une seule version de concurrence lifecycle fait foi;
- aucun double write non atomique n'est possible;
- les comptes historiques possèdent une stratégie explicite;
- l'absence de contexte ne devient jamais `AccountMissing`;
- la responsabilité du Workflow reste inchangée;
- le conflit avec la révocation historique des rôles est neutralisé par
  l'interdiction d'appeler la mutation historique;
- aucune fondation certifiée n'est modifiée.

## Validation

Le gate est documentaire. Aucune campagne de tests ou PostgreSQL n'est
revendiquée. Les résultats certifiés de 4.9B restent la baseline d'entrée.

## Verdict enregistré

```text
4.9C-R1 Historical Coexistence Gate
→ GO CERTIFIÉ
→ FERMÉ

4.9C Persistence Foundation
→ GO CERTIFIÉ
→ FERMÉ

4.9D Runtime Composition
→ SUSPENDU AVANT IMPLÉMENTATION
```

La reprise de 4.9D exige un amendement préalable sur la source Runtime
`AccountRegistry`.
