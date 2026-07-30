# Phase 4.7A-R1 — Decision Context Result Matrix

| Résultat | Contexte | Diagnostic technique | Effet futur |
|---|---|---|---|
| Available | obligatoire | interdit | le Workflow peut évaluer les preuves |
| Unavailable / SourceUnavailable | interdit | obligatoire | arrêt technique, aucune décision métier |
| Unavailable / CorruptedEvidence | interdit | obligatoire | corruption typée, aucune décision métier |

`Missing` est une preuve métier explicite de motif absent. Elle n'est jamais déduite d'une absence de source, d'une exception ou d'une donnée corrompue.
