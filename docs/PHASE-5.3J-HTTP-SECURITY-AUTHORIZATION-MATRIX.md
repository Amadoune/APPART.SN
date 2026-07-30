# Phase 5.3J — HTTP & Security Authorization Matrix

## Principe

L'identité provient exclusivement de `RequireIdentityAccessSession`. Le
Controller futur ne reçoit jamais `actorAccountId`, rôle, capability ou scope
depuis le client.

| Opération | Session IAM | Capability IAM | Règle owner complémentaire | Échec |
|---|---:|---|---|---|
| soumettre un rapport | obligatoire | aucune capability modérateur | acteur = compte de session ; cible activée = Listing | réponse publique homogène |
| lire son rapport | obligatoire | aucune capability modérateur | Query vérifie l'ownership sans révéler l'existence | `NotVisible` homogène |
| lire la queue | obligatoire | `Investigate` | vue filtrée seulement | fail-closed |
| claim queue | obligatoire | `Investigate` | claimOwner = compte de session | fail-closed |
| valider rapport | obligatoire | `Validate` | quatre yeux dans l'orchestrateur | fail-closed |
| enregistrer constat | obligatoire | `Investigate` | quatre yeux dans l'orchestrateur | fail-closed |
| lire dossier | obligatoire | `Investigate` | niveau de vue fermé | fail-closed |
| rendre/superséder décision | obligatoire | `Decide` | quatre yeux et supersession dans l'orchestrateur | fail-closed |
| lire décision | obligatoire | `Decide` ou `Audit` selon purpose | vue filtrée | fail-closed |
| clôturer dossier | obligatoire | `Decide` | politique de clôture owner-local | fail-closed |

## Décisions IAM

| Résultat `ModeratorAuthorizationReaderV1` | Effet HTTP |
|---|---|
| `Allowed` | poursuite |
| `Denied` | refus homogène ; aucun appel owner |
| `Corrupted` | refus fail-closed ; diagnostic interne seulement |
| `DependencyUnavailable` | 503 homogène ; aucun appel owner |

## Quatre yeux

HTTP ne recalcule jamais les acteurs impliqués. Il transmet uniquement
l'AccountId issu de la session. `ForbiddenActor` et `FourEyesViolation`
proviennent exclusivement de l'orchestrateur certifié.

## Interdictions

- AccountId, ModeratorId, rôle ou capability fournis par le client ;
- lecture de l'Aggregate IAM ou ModerationCase ;
- autorisation déduite d'une route, d'un header libre ou d'un rôle déclaré ;
- confusion entre authentification et habilitation ;
- fallback autorisant une opération lors d'une indisponibilité IAM.
