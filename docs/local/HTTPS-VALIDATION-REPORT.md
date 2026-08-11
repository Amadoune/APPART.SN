# HTTPS Validation Report

| Validation | Résultat | Observation |
|---|---|---|
| VirtualHost HTTPS | PASS | `*:443 appart.test` |
| DocumentRoot | PASS | `C:/laragon/www/APPART-REBUILD/public` |
| Syntaxe Apache | PASS | `Syntax OK` |
| Certificat SAN | PASS | `appart.test`, `*.appart.test` |
| Chaîne de confiance locale | PASS | page ouverte sans avertissement TLS |
| `APP_URL` | PASS | `https://appart.test` |
| Home | PASS | rendu navigateur HTTPS |
| `/up` | PASS | `Application up` |
| CSS / JavaScript | PASS | ressources chargées exclusivement en HTTPS |
| Mixed Content | PASS | aucune ressource HTTP observée |
| Console navigateur | PASS | 0 erreur, 0 avertissement |
| Débordement horizontal Home | PASS | largeur document = largeur viewport |
| Cookie IAM Secure réellement accepté | BLOCKED | aucune authentification IAM locale autorisée et exécutable disponible |
| Persistance après reload | BLOCKED | dépend de la preuve précédente |

## Blocage terminal

Le transport est techniquement prêt à accepter le cookie certifié, mais une session IAM réelle ne peut pas être fabriquée pour la preuve. Le repository n'expose pas de parcours d'inscription local ni de credentials de démonstration autorisés. Créer un compte, modifier un hash ou injecter une session aurait dépassé le périmètre et contourné IAM.

## Validation Git

`git diff --check` : PASS. Aucun staging, commit ou tag.
