# Phase 5.2C — HTTP Certification

## Décision proposée

**GO PROPOSÉ.**

Les deux frontières publiques owner sont exécutables et la couche HTTP reste
une adaptation additive sans décision métier, persistence ou reconstruction.

## Endpoints certifiables

| Méthode | URI | Auto-scope | Autorité consommée |
|---|---|---|---|
| GET | `/professional/mandate` | `AccountId` de la session IAM | `ProfessionalMandateResolverV1` |
| GET | `/professional/status` | `AccountId` → mandat résolu → `ProfessionalId` | `ProfessionalPublicStatusReaderV1` |

Les routes sont protégées par `RequireIdentityAccessSession` et le rate limiting
IAM authentifié. Aucun identifiant professionnel n'est accepté du client.

## Mapping HTTP fermé

| Résultat mandat | HTTP | Corps public |
|---|---:|---|
| Resolved | 200 | `status`, `professionalId` |
| NotMandated | 404 | `status` |
| Ambiguous | 409 | `status` |
| Corrupted | 500 | `status` |
| DependencyUnavailable | 503 | `status` |

| Décision statut | HTTP | Corps public |
|---|---:|---|
| Available | 200 | `status` |
| Unavailable | 409 | `status` |
| Missing | 404 | `status` |
| Corrupted | 500 | `status` |
| DependencyUnavailable | 503 | `status` |

Toutes les réponses portent `Cache-Control: no-store, private`,
`Pragma: no-cache` et `X-Content-Type-Options: nosniff`.

## Preuves terminales

| Campagne | Résultat |
|---|---|
| Unit + Feature + Runtime + Architecture ciblés | 11 tests, 134 assertions — PASS |
| Architecture complète | 652 tests, 51 080 assertions — PASS |
| Suite applicative complète | 2 877 tests, 59 504 assertions — PASS |
| Runtime Health | catalogue historique 60 + extension `ProfessionalEndpoint`, `Healthy` — PASS |
| Résolution Laravel | `ProfessionalEndpointRuntimeV1` singleton — PASS |
| PHPStan | 0 erreur — PASS |
| Pint | PASS |
| `git diff --check` | PASS |

## Garanties

- aucun changement des contrats publics, du resolver, du reader ou de la source
  owner ;
- migration 062, mapper PostgreSQL et Persistence inchangés ;
- aucun Aggregate, Event, Delivery, Outbox, Search ou Projection ;
- aucune migration, table ou schéma ;
- controller fermé sans branche métier ;
- Runtime Health étendu uniquement par composition de `ProfessionalEndpoint`,
  sans modification du provider Runtime historique.

## Recommandation

**Phase 5.2C — HTTP Foundation : GO PROPOSÉ.**
