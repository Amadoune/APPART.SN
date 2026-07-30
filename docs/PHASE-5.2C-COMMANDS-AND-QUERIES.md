# Phase 5.2C — Commands & Queries

## Enveloppe commune des Commands

Chaque Command porte conceptuellement :

- `intentId` ;
- `professionalId` ;
- `actorAccountId` auto-scopé à terme ;
- `expectedVersion` ;
- `occurredAt` ;
- `policyVersion` ;
- payload fermé propre à l’opération.

Les champs inconnus sont interdits. Aucun Command ne reçoit credentials,
sessionId, token, preuve brute ou diagnostic interne.

## ProfessionalPublicProfile Commands

| Commande | Effet propriétaire | Résultats fermés |
|---|---|---|
| CreateProfessionalPublicProfile | crée le Draft unique | Applied, AlreadyApplied, DivergentIntent, ProfessionalMissing, ProfileAlreadyExists, InvalidInput |
| ReviseProfessionalPublicProfile | ajoute une révision | Applied, AlreadyApplied, DivergentIntent, ProfileMissing, VersionConflict, InvalidInput |
| PublishProfessionalPublicProfile | Draft/Hidden → Visible | Applied, AlreadyApplied, DivergentIntent, ProfileMissing, Incomplete, Unavailable, VersionConflict |
| HideProfessionalPublicProfile | Visible → Hidden | Applied, AlreadyApplied, DivergentIntent, ProfileMissing, AlreadyHidden, VersionConflict |

`Unavailable` ne peut être calculé en production avant certification de la
frontière publique F-05.

## ProfessionalVerification Commands

| Commande | Résultats fermés |
|---|---|
| RequestProfessionalVerification | Applied, AlreadyApplied, DivergentIntent, ProfessionalMissing, AlreadyPending, InvalidEvidenceReference, VersionConflict |
| GrantProfessionalVerification | Applied, AlreadyApplied, DivergentIntent, VerificationMissing, NotPending, PolicyMismatch, VersionConflict |
| RejectProfessionalVerification | Applied, AlreadyApplied, DivergentIntent, VerificationMissing, NotPending, PolicyMismatch, VersionConflict |
| ExpireProfessionalVerification | Applied, AlreadyApplied, DivergentIntent, VerificationMissing, NotVerified, TooEarly, VersionConflict |
| RevokeProfessionalVerification | Applied, AlreadyApplied, DivergentIntent, VerificationMissing, NotVerified, PolicyMismatch, VersionConflict |

Les diagnostics détaillés restent internes ; les résultats publics ne révèlent
ni existence de documents ni informations de reviewer.

## Professional Core Commands

5.2C réutilise uniquement les comportements historiques :

- AddEstablishment ;
- RemoveEstablishment ;
- GrantRepresentativeMandate ;
- RevokeRepresentativeMandate.

Aucune commande de statut, suspension ou réactivation n’est redéfinie. Aucun
mandat professionnel n’accorde implicitement une délégation Listing F-19.

## Queries

| Query | Owner lu | Résultat fermé |
|---|---|---|
| GetProfessionalPublicProfile | PublicProfile | Found, Hidden, Missing, Unavailable |
| GetProfessionalProfileRevision | PublicProfile | Found, Missing |
| GetProfessionalVerificationSummary | Verification | Found, Missing, Unavailable |
| GetProfessionalPublicPortfolio | Portfolio | Found, Empty, Missing, Unavailable |
| GetProfessionalAvailability | composition | Available, AccountUnavailable, ProfessionalStatusUnavailable, ProfileHidden, VerificationRequired, Corrupted |
| ListProfessionalEstablishments | Professional historique | Found, Empty, Missing |
| ListRepresentativeMandates | Professional historique | Found, Empty, Missing |

`GetProfessionalAvailability` est un contrat documentaire non activable tant
que la réserve F-05 n’est pas levée.

## Erreurs et diagnostics

- erreurs de validation : fermées et sans écho de données sensibles ;
- conflits : identifiés par catégorie, sans SQL ni exception technique ;
- indisponibilité : publique et homogène ;
- corruption : diagnostic interne séparé, réponse publique fail-closed ;
- aucune Query publique ne permet l’énumération d’Accounts ou de preuves.
