# A-5.1-IAM-PROFILE-01 — Profile Decision

## 1. Critères

| Critère | Résultat |
|---|---|
| frontières gelées identifiées | SATISFAIT |
| impacts Profile documentés | SATISFAIT |
| compatibilités démontrées | SATISFAIT |
| approches incompatibles démontrées | SATISFAIT |
| nouveaux amendements éventuels identifiés | SATISFAIT |
| mutation implicite du gel 4.9 | AUCUNE |

## 2. Décision

Le modèle retenu sépare explicitement :

- Account historique gelé ;
- User Profile canonique et versionné ;
- Identity Claim Registry pour l'unicité ;
- challenges Profile pour la revérification ;
- catalogue événementiel et delivery propres.

Il préserve Account Status, Snapshot V1, AccountRegistry, migrations 041–043,
Runtime Health 58, HTTP et Outbox gelés.

```text
A-5.1-IAM-PROFILE-01
→ AUDIT COMPLET
→ GO PROPOSÉ
```

Ce GO documentaire n'autorise aucune implémentation. Il rend seulement le
modèle Profile éligible au futur périmètre de 5.1 après décision d'autorité et
résolution concomitante de Closure.
