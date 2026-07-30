# Phase 4.7A-R1 — Decision Context Specification

## Contrat V1

`AdministrativeActionDecisionContext` contient exactement :

- `contractVersion = V1` ;
- `reasonEvidence` : `Present` ou `Missing` ;
- `authority.disposition` : `DirectRecording` ou `IndependentApprovalRequired` ;
- `authority.author` ;
- `authority.decisionActor`.

Le texte du motif n'est jamais transporté.

## Autorité

- `DirectRecording` impose `author == decisionActor`.
- `IndependentApprovalRequired` impose `author != decisionActor`.

Les factories refusent toute combinaison incohérente. Aucune valeur par défaut, identité implicite ou acteur système n'existe.

## Intégrité

Le checksum SHA-256 couvre, dans un ordre canonique, version, preuve du motif, disposition, auteur et acteur de décision. Il vérifie l'intégrité du contrat sans prendre de décision métier.
