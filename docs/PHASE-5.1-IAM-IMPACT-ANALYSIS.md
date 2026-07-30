# A-5.1-IAM-01 — Impact Analysis

## 1. Impact des fonctions compatibles

| Surface | Impact admissible futur | Impact interdit |
|---|---|---|
| Account | appel des comportements existants | nouveau champ/méthode/invariant |
| AccountRegistry | usage de find/add/save | nouvelle méthode ou sémantique |
| Historical Account | round-trip existant | Snapshot V2 implicite ou SQL direct |
| migration 041 | aucun | toute modification |
| migration 042 | lecture par adapter IAM et writes via repository | ajout de colonne/index/table dans le fichier |
| migration 043 | aucun | nouveau type/mapping Account Status |
| Event V1 | aucun | ajout/champ/version |
| Outbox/Delivery | aucun pour le socle auth | publication forcée d'événements non Status |
| Runtime Health | rester à 58 | nouveau requirement implicite |
| Status HTTP | aucun | route, middleware, result mapping modifiés |
| IAM HTTP futur | routes séparées et owner distinct | réutilisation du controller Status |

## 2. Impacts potentiels à cadrer en Phase 5.1

Même compatibles, les fonctions additives devront décider :

- source de lookup email/téléphone et protection contre l'énumération ;
- password hashing/verifying et migration algorithmique ;
- rate limits, lockout et anti-bruteforce ;
- rotation, fixation, révocation et durée des sessions ;
- invalidation des sessions après password/status change ;
- challenge recovery, expiration, usage unique et anti-replay ;
- CSRF, cookies, SameSite, secure transport et audit ;
- autorité d'attribution des rôles ;
- preuve/version de consentement ;
- delivery des vérifications sans secrets dans l'Outbox ;
- rétention des tentatives et sessions.

Ces sujets sont nouveaux mais ne justifient pas de modifier le périmètre gelé.

## 3. Impact Profile Amendment

L'amendement profil doit décider au minimum :

- mutabilité du nom, email et téléphone ;
- revalidation et perte temporaire de verified state ;
- maintien des unicités et protection anti-takeover ;
- version du snapshot ou persistence additive ;
- nouveaux événements et consommateurs ;
- effet sur login, recovery, sessions et notifications ;
- audit, PII, rectification et historique ;
- compatibilité de `AccountRegistry` et du mapper gelés.

Sans cette décision, une API de profil ne peut offrir que la lecture.

## 4. Impact Closure Amendment

L'amendement fermeture doit décider :

- Closed versus anonymized versus erased ;
- différence avec Suspended ;
- réouverture autorisée ou non ;
- durée de rétention des credentials, roles, consents et verification proofs ;
- invalidation atomique des sessions/challenges ;
- réservation ou libération email/téléphone ;
- impact sur Professional, Listing, Lead, Reservation, Favorites et Audit ;
- événements nécessaires et confidentialité ;
- traitement du Snapshot V1 et des migrations ;
- comportement HTTP, login, recovery et Account Status.

Il s'agit d'un impact cross-domain critique. Une suppression directe ou une
suspension renommée « fermeture » sont interdites.

## 5. Risques

| Risque | Sévérité | Gate |
|---|---|---|
| comparer un mot de passe clair comme `PasswordHash` | critique | verifier infrastructure dédié |
| exposer le hash par un reader | critique | résultat booléen/AccountId seulement |
| réutiliser verification token pour recovery | élevé | store challenge séparé |
| modifier `AccountRegistry` par commodité | critique | architecture test |
| publier les Domain Events historiques dans l'Outbox Status | critique | catalogue owner distinct ou absence d'event |
| compter nouvelle auth dans les 58 requirements | élevé | phase Runtime dédiée si nécessaire |
| implémenter closure par delete cascade | critique | amendement closure |
| muter profil sans revérification | critique | amendement profile |

## 6. Non-impact confirmé pendant A-5.1-IAM-01

Aucun fichier PHP, SQL, route, configuration, Provider, test, migration,
Runtime ou Outbox n'est modifié par cet audit.
