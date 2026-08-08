# Phase 5.8C — Experience & Acceptance — Persistence Risk Register

| Risque | Maîtrise | État |
|---|---|---|
| Mélange entre streams | Clé primaire scope/stream/revision | Couvert |
| Replay divergent | Checksum canonique et DivergentRevision | Couvert |
| Course sur première révision | Advisory lock transactionnel | Couvert |
| Lecture temporelle ambiguë | Ordre total déterministe | Couvert |
| Rollback appelant compromis | Savepoints locaux | Couvert |
| Introduction de logique UX | Décisions limitées au catalogue structurel | Couvert |
| Accès cross-domain | Gate Architecture | Couvert |
| Altération de migrations gelées | Migration 090 additive uniquement | Couvert |

