# Phase 4.4 — Candidate Comparison Matrix

Échelle : 1 faible, 5 fort. Pour le risque, 5 signifie risque élevé.

| Candidat | Preuve actuelle | Autonomie | Maturité | Valeur verticale | Risque | PostgreSQL | Décision |
|---|---|---:|---:|---:|---:|---|---|
| Lead Lifecycle | Aggregate, 4 états, 4 commandes, 4 événements, 3 ports | 5 | 4 | 5 | 2 | absent | **retenu** |
| Payment Lifecycle | Order, Payment, Benefit, 7 use cases, 8 événements | 4 | 4 | 5 | 5 | absent | différé |
| Account Lifecycle | Account, credentials, rôles, consentements, 12 use cases | 4 | 5 | 5 | 5 | absent | à décomposer |
| Moderation Lifecycle | ModerationCase, 5 use cases, 5 événements, 2 ports | 4 | 4 | 4 | 4 | absent | après contrat d'effets Listing |
| Professional Lifecycle | Professional, établissements, mandats, 7 use cases | 4 | 4 | 4 | 3 | absent | candidat ultérieur |
| Administrative Action | Aggregate, événements et PostgreSQL existant | 4 | 5 | 3 | 3 | présent | capacité support |
| Geography Lifecycle | Place, 5 commandes, 5 événements | 4 | 4 | 3 | 3 | absent | capacité référentielle |
| Media Lifecycle | Aggregate, 7 use cases, événements et PostgreSQL | 4 | 5 | 4 | 3 | présent | déjà largement fondé |
| Offer Lifecycle | aucun modèle/module | 1 | 0 | 4 | 5 | absent | non cadrable |
| Application Lifecycle | aucun Aggregate métier identifié | 1 | 0 | 4 | 5 | absent | non cadrable |
| Visit Lifecycle | aucune visite ; seulement `VisitorIdentity` dans Leads | 1 | 0 | 3 | 5 | absent | non cadrable |
| Contract Lifecycle | aucun modèle/module | 1 | 0 | 4 | 5 | absent | non cadrable |
| Messaging Lifecycle | aucun modèle ; `ContactMessage` est un Value Object Lead | 1 | 1 | 4 | 5 | absent | non cadrable |

## Pourquoi Lead avant Payment

Lead apporte immédiatement le flux « annonce consultée → contact transmis » et possède une machine d'état minimale. Payment exige d'abord de décider si la capacité propriétaire est Order, Payment ou Benefit, puis de traiter PSP, remboursement et cohérence monétaire. Le coût de cadrage et le risque d'un succès partiel sont nettement supérieurs.
