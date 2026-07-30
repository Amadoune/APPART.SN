# Phase 5.3J — HTTP & Security Threat Model

| Menace | Actif | Mesure obligatoire | État |
|---|---|---|---|
| usurpation d'acteur | décisions et quatre yeux | AccountId issu uniquement de la session IAM | frontière disponible |
| élévation de privilège | opérations privées | `ModeratorAuthorizationReaderV1`, seul `Allowed` poursuit | disponible |
| énumération de rapports | confidentialité reporter | `Visible`/`NotVisible` homogénéisés | **reader absent** |
| fuite de preuves ou acteurs | dossier privé | Query filtrée owner-scoped | **reader absent** |
| mass assignment | Commands | allowlist stricte et rejet des champs inconnus | stratégie disponible |
| double mutation | intégrité | Idempotency-Key UUID et intent checksum | disponible |
| lost update | intégrité | expectedVersion validé ; 409 sur conflit | disponible |
| contournement quatre yeux | gouvernance | acteur auto-scopé, invariant dans orchestrateur | disponible |
| brute force / abus | disponibilité | rate limiter HMAC AccountId/IP sans PII | stratégie disponible |
| CSRF | session cookie | middleware CSRF conservé sur mutations | stratégie disponible |
| fuite technique | secrets/architecture | mapper fermé, aucune exception/SQL/PDO | stratégie disponible |
| cache partagé | confidentialité | `no-store`, aucune réponse privée cachable | stratégie disponible |
| confusion de cible | domaine Listing | target type fermé à Listing en 5.3J initial | disponible |
| contournement handoff | Listing F-01 | HTTP n'appelle jamais le Gateway de sanction | règle certifiée |

## Données sensibles

Ne doivent jamais être retournés :

- identités des acteurs hors vue privée explicitement autorisée ;
- contenu brut de preuve ;
- SQLSTATE, SQL, nom de table ou exception ;
- diagnostics Runtime ;
- payload Outbox, checksum interne ou lease owner technique ;
- cookie, token, secret de session ou empreinte HMAC ;
- état interne Listing.

## Rate limiting

La clé de risque est calculée par HMAC sur l'AccountId issu de la session. En
absence de compte valide, une empreinte HMAC de l'adresse réseau peut protéger
le point d'entrée sans stocker la valeur brute. La clé et la valeur d'entrée ne
sont jamais exposées dans une réponse ou un log métier.

## Risque résiduel bloquant

Sans Queries exécutables, l'HTTP devrait filtrer directement des snapshots de
persistence. Cette option créerait une fuite d'autorité et de données. Le risque
est bloquant et impose le NO GO.
