# IAM Web Entry Experience 01 — Validation Report

## Contrôles exécutés

| Contrôle | Résultat | Preuve |
|---|---|---|
| Accueil local | PASS | `http://appart.test/` rendu dans le navigateur |
| État DOM du shell | PASS | actions IAM présentes mais de type `button`, sans destination |
| Endpoint IAM Login | PASS | route et contrat HTTP présents dans le repository |
| Workspace protégé | PASS | `GET /authoring/workspace` utilise `RequireIdentityAccessSession` |
| Cookie de session | BLOCKED | `__Host-appart_session` est toujours émis `Secure` |
| HTTPS local | FAIL | `https://appart.test/` retourne `ERR_SSL_PROTOCOL_ERROR` |
| Parcours réel complet | BLOCKED | aucun cookie Secure ne peut être établi sur HTTP |

## Campagnes

Unit, Feature, Architecture, PHPStan, Pint et Vite n'ont pas été relancés : aucune implémentation n'a été produite et la première condition d'intégration navigateur est bloquante. Revendiquer ces campagnes n'aurait pas démontré une session réelle sur le parcours demandé.

`git diff --check` est la seule validation terminale requise sur les livrables documentaires de ce NO GO.

## Critères produit

- « Se connecter » ouvre un vrai parcours IAM : **FAIL**.
- session utilisateur réelle : **BLOCKED**.
- « Déposer une annonce » ouvre le workspace : **FAIL**.
- aucune simulation / donnée fictive : **PASS**, aucune simulation ajoutée.
- responsive et console : **NOT_TESTED**, parcours non matérialisé.
