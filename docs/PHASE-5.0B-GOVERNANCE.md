# Phase 5.0B — Governance

## 1. États officiels

| État | Sens |
|---|---|
| PROPOSÉ | dossier soumis, aucune autorisation |
| OUVERT | seul périmètre autorisé à travailler |
| SUSPENDU | conservation des acquis, aucune progression |
| NO GO | critères non satisfaits |
| GO CERTIFIÉ | critères satisfaits par preuves |
| FERMÉ | aucun travail additionnel dans le périmètre |
| GELÉ | modification interdite sans amendement versionné |

`GO` n'implique pas `GELÉ` sauf si la décision le dit. `GO FINAL` suivi du
prononcé d'autorité clôt et gèle le périmètre déclaré.

## 2. Autorités

| Responsabilité | Autorité |
|---|---|
| invariant et écriture métier | Aggregate Owner |
| nom, payload et version | Event Owner |
| schéma/read model | Projection Owner |
| ligne/catalogue producteur | Outbox Owner logique |
| route, auth, validation et mapping | HTTP Owner |
| décision GO/NO GO/gel | autorité de certification |
| conflit cross-domain | revue architecture, décision consignée |

Les owners sont ceux de `PHASE-5.0A-OWNERSHIP-MATRIX.md`. Un owner ne peut
être transféré implicitement par un adapter partagé.

## 3. Règles de changement

Un changement est :

- **documentaire non normatif** s'il corrige forme, lien ou explication sans
  changer un droit, un contrat ou une décision ;
- **documentaire normatif** s'il change une frontière, un owner, une gate ou
  une interprétation : certification requise ;
- **additif compatible** s'il ajoute un consumer ou adapter sans modifier le
  contrat gelé : analyse d'impact et certification ciblée ;
- **amendement** s'il touche une capacité gelée ;
- **nouvelle capacité** s'il possède son propre owner et son propre cycle de
  certification.

## 4. Ouverture d'un amendement

Un amendement peut être ouvert uniquement si :

1. le besoin ne peut être satisfait par le contrat gelé existant ;
2. la capacité et la version ciblées sont identifiées ;
3. l'owner et les consommateurs affectés sont inventoriés ;
4. alternatives sans mutation et décision de rejet sont documentées ;
5. compatibilité, migration, rollback, replay et sécurité sont analysés ;
6. le plan de recertification est défini avant toute implémentation ;
7. l'autorité prononce explicitement l'ouverture.

Le nom suit `PHASE-x.y-AMENDMENT-n-SUJET-Vn.md` ou la convention Rn déjà
établie par la phase. Une version n'est jamais réécrite après certification.

## 5. Interdictions

- aucune modification « opportuniste » d'un contrat gelé ;
- aucun changement Runtime/Outbox caché dans une phase métier ;
- aucun owner partagé ou « plateforme » pour une décision métier ;
- aucune certification basée uniquement sur une documentation ;
- aucun PASS attribué à un skip, timeout ou environnement indisponible ;
- aucun lancement de la phase suivante avant le prononcé officiel ;
- aucune réécriture d'une certification historique.

## 6. Gestion documentaire

Chaque phase met à jour :

- son dossier de décision et ses preuves ;
- les deux registres si un gel ou amendement apparaît ;
- la baseline qualité avec date, commandes et environnement ;
- `CHANGELOG.md` ;
- `README.md`, `ROADMAP.md` ou le Master Blueprint uniquement si leur vue
  synthétique change.

Le diff final doit être relu par périmètre. Un worktree déjà sale impose une
liste explicite des fichiers attribuables au sprint ; il ne justifie jamais
l'altération de changements préexistants.
