# Phase 5.2C — Contracts Foundation

## Statut et recommandation

**GO CERTIFIÉ — FERMÉE.**

Ce dossier est normatif et exclusivement documentaire. Il ne crée aucune
interface PHP, migration, classe Event, persistence, composition Runtime ou
surface HTTP.

## Autorités contractuelles

| Autorité | Nature | Responsabilité exclusive |
|---|---|---|
| Professional historique | Aggregate existant | identité légale, établissements, mandats |
| ProfessionalPublicProfile | Aggregate additif futur | présentation publique et visibilité |
| ProfessionalVerification | Aggregate additif futur | cycle et décision de vérification |
| ProfessionalPublicPortfolio | projection future | références publiques de Listings éligibles |
| ProfessionalAvailability | composition pure future | décision fail-closed, sans persistence |

## ProfessionalPublicProfile

### État

`Draft`, `Visible`, `Hidden`.

### Données autorisées

- `ProfessionalId` immuable ;
- nom public, description, catégories et langues ;
- contacts expressément marqués publiables ;
- références opaques de médias sûrs ;
- version, revision number, policy version et timestamps.

### Invariants

- un seul profil par `ProfessionalId` ;
- création initiale en `Draft` ;
- visibilité impossible sans complétude minimale ;
- une révision ne réécrit jamais l’historique ;
- `Hidden` conserve les données et références ;
- aucun claim IAM privé n’est copié ;
- aucune preuve de vérification n’est stockée dans le profil.

## ProfessionalVerification

### États

`Unverified`, `Pending`, `Verified`, `Rejected`, `Expired`, `Revoked`.

### Transitions autorisées

| Depuis | Commande | Vers |
|---|---|---|
| Unverified, Rejected, Expired, Revoked | RequestVerification | Pending |
| Pending | GrantVerification | Verified |
| Pending | RejectVerification | Rejected |
| Verified | ExpireVerification | Expired |
| Verified | RevokeVerification | Revoked |

Toute autre transition produit un résultat fermé non appliqué.

### Confidentialité

Le contrat métier conserve uniquement références opaques, catégorie de preuve,
policy version, disposition, autorité de décision et dates. Le document brut,
son object key, les diagnostics fournisseur et les données biométriques sont
interdits.

## ProfessionalPublicPortfolio

Projection strictement read-only :

- clé `ProfessionalId` ;
- références de Listings publiés ;
- ordre public optionnel appartenant à la projection ;
- état d’éligibilité et version de source ;
- checkpoint monotone et rebuild déterministe.

Elle ne possède ni Listing, ni Property, ni ownership, ni délégation F-19.

## Idempotence et concurrence

Toutes les mutations futures devront porter :

- `intentId` UUID ;
- checksum canonique de l’intention ;
- expected version ;
- actor reference et occurredAt explicites.

Résolution normative :

- même intent + même checksum : `AlreadyApplied` ;
- même intent + checksum différent : `DivergentIntent` ;
- version obsolète : `VersionConflict` ;
- concurrence : un seul `Applied`, les autres convergent sans écriture
  partielle ;
- rollback : état, révision et intent sont atomiques chez un seul owner.

## Frontières gelées

F-05, F-17/F-18, F-19/F-20 et F-21/F-22 restent inchangés. Les contrats 5.2C
ne confèrent aucune autorité de lecture interne ou d’écriture sur ces capacités.

`A-5.2C-PROFESSIONAL-STATUS-READ-BOUNDARY-01` reste identifié, non ouvert et
bloquant avant activation de ProfessionalAvailability, Runtime ou HTTP.
