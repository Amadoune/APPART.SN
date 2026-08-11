# APPART.TEST Local IAM Principal Provisioning 01

## Question auditée

Existe-t-il un chemin certifié permettant d'obtenir un principal IAM local authentifiable dans le navigateur, sans SQL, injection de session ni modification IAM ?

## Inventaire

| Surface | État | Qualification |
|---|---|---|
| `RegisterAccount` | PRESENT | use case owner-scoped qui construit l'Account et appelle `AccountRegistry::add` |
| `AccountRegistry` | PRESENT | port Application |
| `PostgreSqlAccountRepository` | PRESENT | adapter lié au port dans la composition existante |
| génération autoritative d'un `PasswordHash` depuis un secret local | MISSING | aucun port/service certifié trouvé |
| commande locale de provisioning IAM | MISSING | aucune commande Artisan existante |
| inscription HTTP | MISSING | aucune route publique de registration |
| Runtime HTTP Login exécutable | BLOCKED | binding de production vers `FailClosedIdentityAccessHttpRuntime` |
| inspection de session réelle | BLOCKED | le même fallback retourne toujours une inspection invalide |

## Décision

Le repository contient les briques historiques de persistance d'un Account, mais pas une chaîne certifiée complète :

`credential clair → hash autoritatif → RegisterAccount → login HTTP → session`.

Le plus petit ajout ne serait donc pas un simple seed local. Il faudrait au minimum qualifier une autorité de hashing/provisioning et matérialiser l'adapter réel de `IdentityAccessHttpRuntime` pour Login et Session Inspection. Cela constitue un amendement IAM, explicitement interdit par la mission.

Créer uniquement un compte avec un hash construit dans une commande locale déplacerait l'autorité de sécurité dans l'outillage de développement. Créer uniquement le compte ne lèverait par ailleurs pas le fallback HTTP fail-closed.
