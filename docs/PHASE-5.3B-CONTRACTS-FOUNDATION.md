# Phase 5.3B — Contracts Foundation

## Statut

**Recommandation : GO PROPOSÉ**

Ce dossier est exclusivement documentaire. Il définit les futures frontières
publiques V1 de `Moderation & Reports`, sans créer d'interface PHP, Aggregate,
Runtime, Persistence, HTTP, Event concret, Delivery ou Outbox.

Le Blueprint 5.3 certifié reste inchangé.

## Autorités contractuelles

| Autorité | Owner | Responsabilité exclusive |
|---|---|---|
| Moderation Case | `ModerationReports.ModerationCase` | rapports, qualifications, constats, décisions et clôture |
| Moderation Queue | `ModerationReports.ModerationQueue` | projection de travail et leases |
| Moderation Decision | `ModerationReports.ModerationCase` | décision courante et historique append-only |
| Target Action | domaine propriétaire de la cible | application réelle de l'action demandée |
| Transverse Audit | `AdministrationAudit` | journal transverse |

`ModerationDecisionStore` est une vue propriétaire du même owner
`ModerationCase`. Il ne constitue ni un second Aggregate ni une seconde autorité
de décision.

## Règles communes des contrats V1

Tout futur contrat technique issu de cette Foundation devra être :

- immutable ;
- explicitement suffixé `V1` ;
- indépendant de Laravel, PDO, PostgreSQL, HTTP et du transport ;
- fermé sur ses entrées et ses résultats ;
- sérialisable de façon canonique ;
- sans exception technique dans sa surface publique ;
- fail-closed face à une donnée absente, corrompue ou indisponible.

## Enveloppe des Commands

Chaque Command porte uniquement :

- `intentId` UUID ;
- identifiant propriétaire nécessaire à l'opération ;
- `actorAccountId` issu d'une frontière IAM certifiée ;
- `expectedVersion` pour toute mutation d'un dossier existant ;
- `occurredAt` ;
- `policyVersion` ;
- payload fermé propre à l'opération.

Un Command ne reçoit jamais :

- session, cookie, credential ou token ;
- rôle déclaré par le client ;
- exception ou diagnostic technique ;
- référence SQL, Repository ou classe d'Aggregate ;
- preuve brute, secret ou PII non indispensable.

## Identifiants et types normatifs

Les contrats utilisent des identifiants opaques :

`ModerationCaseId`, `ModerationReportId`, `ModerationFindingId`,
`ModerationDecisionId`, `ModerationQueueItemId`, `ModerationIntentId`,
`AccountId`, `TargetId` et `TargetActionId`.

`ModerationTargetReferenceV1` est une union fermée :

- `Listing` ;
- `Media` ;
- `Account` ;
- `ProfessionalProfile`.

Une variante n'est activable que lorsque ses frontières externes sont
certifiées pendant 5.3C.

## Invariants contractuels

### Quatre yeux

- le reporter ne valide pas son propre rapport ;
- le validateur ne produit pas le constat servant seul à sa décision ;
- l'auteur d'un constat ne décide pas sur ce constat ;
- un acteur impliqué dans le rapport ne peut être le décideur final ;
- l'identité des acteurs est auto-scopée et jamais choisie par HTTP ;
- une violation produit `ForbiddenActor` ou `FourEyesViolation`, sans écriture.

### Idempotence

- même `intentId` et même checksum : `AlreadyApplied` ;
- même `intentId` et checksum différent : `DivergentIntent` ;
- un intent terminal n'est jamais réinterprété ;
- le checksum couvre l'ensemble des données sémantiques du Command ;
- aucun horodatage serveur implicite ne modifie le checksum.

### Concurrence

- `expectedVersion` est obligatoire après création ;
- une version obsolète produit `VersionConflict` ;
- un seul concurrent peut produire `Applied` ;
- les autres convergent vers un résultat fermé ;
- aucune écriture partielle n'est autorisée.

### Append-only

- rapports, validations, constats, décisions et intents restent historisés ;
- une décision est remplacée uniquement par supersession explicite ;
- une clôture ne détruit aucune preuve métier ;
- aucune correction ne réécrit silencieusement une révision antérieure.

### Confidentialité

- aucune donnée sensible n'est exposée par les Queries publiques ;
- les raisons détaillées, preuves et identités d'acteurs restent dans les vues
  privées autorisées ;
- aucun Event candidat ne contient de texte libre, PII, token ou URI signée ;
- diagnostics internes et résultats publics sont séparés.

### Anti-énumération et fail-closed

- `ReadOwnModerationReportV1` ne confirme jamais un rapport appartenant à un
  autre compte ;
- une cible indisponible ou corrompue n'est jamais présumée valide ;
- une habilitation inconnue interdit l'opération ;
- les erreurs techniques sont traduites en résultats fermés homogènes.

## Versionnement

- une évolution additive compatible peut introduire un champ optionnel seulement
  après démonstration de compatibilité ;
- tout changement de sens, de résultat ou d'invariant exige une version majeure ;
- V1 ne peut pas être modifié après son gel final sans amendement versionné ;
- un consumer doit rejeter une version inconnue fail-closed.

## Frontières externes

Les contrats `ModerationTargetGateway` et `ModerationAuditGateway` définissent
les besoins de 5.3, mais ne confèrent aucune autorité sur les domaines externes.
Leur raccordement dépend des Boundary Gates 5.3C.

Sont donc bloquants avant implémentation correspondante :

- décision publique d'habilitation IAM/modération ;
- reader public de chaque type de cible activé ;
- command gateway public de chaque target owner ;
- append public d'Administration Audit.

## Livrables normatifs associés

- `PHASE-5.3B-COMMANDS-AND-QUERIES.md` ;
- `PHASE-5.3B-RESULT-MATRIX.md` ;
- `PHASE-5.3B-PORT-MATRIX.md` ;
- `PHASE-5.3B-EVENT-CATALOG.md` ;
- `PHASE-5.3B-CONTRACT-CERTIFICATION.md`.

## Hors périmètre

Aucun schéma de table, mapper, lease SQL, transport, sérialiseur, route, code
HTTP, provider, binding Laravel ou logique métier concrète n'est défini ici.
