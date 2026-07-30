# Phase 5.2C — Event Catalog candidat

## Statut

Catalogue **documentaire candidat uniquement**. Aucun Event concret, transport,
routing, delivery ou Outbox n’est autorisé.

## Événements candidats minimaux

| Type candidat | Event Owner | Consumer démontré potentiel | Payload maximal |
|---|---|---|---|
| `professional.profile.published.v1` | Professionals.Profile | portfolio/public projection | eventId, professionalId, aggregateVersion, policyVersion, timestamps |
| `professional.profile.hidden.v1` | Professionals.Profile | portfolio/public projection | mêmes champs |
| `professional.verification.granted.v1` | Professionals.Verification | availability/public badge | disposition, professionalId, version, policyVersion, timestamps |
| `professional.verification.rejected.v1` | Professionals.Verification | private workflow only | disposition minimale, sans motif libre |
| `professional.verification.expired.v1` | Professionals.Verification | availability/public badge | identité et version |
| `professional.verification.revoked.v1` | Professionals.Verification | availability/public badge | identité et version |

## Événements historiques

`ProfessionalRegistered`, `EstablishmentAdded`, `EstablishmentRemoved`,
`MandateGranted` et `MandateRevoked` existent déjà. Contracts Foundation :

- ne les modifie pas ;
- ne crée pas de V1 transport concret ;
- interdit un doublon sémantique ;
- exige un audit de consumers avant toute inclusion dans un futur catalogue.

Les événements F-05 `professional.status.suspended` et `reactivated` restent
exclusivement propriétaires de Professional Status.

## Métadonnées normatives

Tout futur événement retenu devra avoir :

- owner unique ;
- eventId et checksum déterministes ;
- aggregate version strictement positive ;
- occurredAt/recordedAt explicites ;
- correlationId/causationId opaques ;
- payload versionné et fermé ;
- aucune PII privée, preuve, token, object key ou diagnostic interne.

## Politique de minimisation

Un événement sans consumer démontré est supprimé du catalogue avant
implémentation. Les changements purement éditoriaux du profil peuvent rester
internes si aucune projection ne les consomme.

## Replay et compatibilité

- replay idempotent par eventId/checksum ;
- divergence mise en quarantaine ;
- ordre par aggregate version ;
- événement inconnu ou version inconnue rejeté fail-closed ;
- aucune mutation cross-domain pendant consumption ;
- compensation explicite, jamais transaction ACID cross-domain.
