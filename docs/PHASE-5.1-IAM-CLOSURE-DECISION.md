# A-5.1-IAM-CLOSURE-01 — Closure Decision

## 1. Critères

| Critère | Résultat |
|---|---|
| frontières gelées identifiées | SATISFAIT |
| Closed/Deleted/Anonymized distingués | SATISFAIT |
| Suspended démontré non équivalent | SATISFAIT |
| rétention et réouverture cadrées | SATISFAIT |
| impacts cross-domain documentés | SATISFAIT |
| compatibilités/incompatibilités démontrées | SATISFAIT |
| amendement Erasure identifié | SATISFAIT |
| mutation implicite des capacités 4.9 | AUCUNE |

## 2. Décision

Account Closure est une autorité additive et orthogonale à Account Status. Le
close interdit l'accès, invalide les sessions et préserve les références ; il
ne supprime ni n'anonymise Historical Account.

L'effacement irréversible est réservé à
`A-5.1-IAM-ERASURE-01`. Cette réserve ne bloque pas le modèle métier Closed,
mais interdit toute implémentation d'anonymisation/destruction avant sa
certification.

```text
A-5.1-IAM-CLOSURE-01
→ AUDIT COMPLET
→ GO PROPOSÉ
```

Ce GO documentaire n'ouvre pas Phase 5.1. Seule l'autorité peut certifier les
deux amendements puis autoriser le réexamen de son ouverture.
