# Phase 5.1C — Persistence Compatibility

## 1. Matrice 5.1B

| Contrat | Preuve persistence |
|---|---|
| Authentication Attempts | compteur/lockout versionné, intent/checksum, HMAC |
| Sessions | state/version, rotation link, expirations et checkpoint |
| Recovery | challenge hash, terminal states, unique active |
| User Profile | contacts protégés, version et seed boundary |
| Identity Claims | réservation/activation, unique fingerprint permanente |
| Contact Changes | challenge hash, claim reference et transitions |
| Profile Revisions | append-only, unique result version |
| Closure | Open/Requested/Closed/Reopened, checkpoint et rétention |
| Availability | aucune persistence, conforme |

## 2. Frontières Phase 4.9

| Élément gelé | Garantie |
|---|---|
| Account | aucune classe ou table mutée |
| AccountRegistry | aucune méthode ajoutée |
| Historical Account | lecture seed différée à 5.1D seulement |
| Snapshot V1 | aucun mapper ou champ modifié |
| 041 | aucun DDL/DML |
| 042 | aucun DDL/DML/trigger/FK |
| 043 | aucun DDL/DML/type owner |
| Account Status | aucune nouvelle state/transition |
| Runtime Health 58 | aucune composition |
| HTTP/Outbox Status | aucun objet créé |

## 3. Absence de double autorité

- avant 5.1D, Profile/Claims sont vides et ne sont autorité de rien ;
- 5.1C certifie uniquement leur capacité de stockage ;
- Account reste source historique ;
- le cutover explicite et son rapport appartiennent à 5.1D ;
- aucun Repository 5.1C ne lit/fallback automatiquement vers 042 ;
- aucune vue SQL ne joint les deux schémas.

## 4. Owners et transactions

Chaque Repository écrit uniquement ses tables. Les opérations nécessitant
plusieurs owners — recovery + password + session, contact + claim + profile +
revision, closure + session checkpoint — ne sont pas exécutées en 5.1C.

5.1C exige que les repositories puissent participer à une transaction externe
sans commit interne, mais la transaction métier et sa composition restent
5.1F. Ce mécanisme ne transfère pas l'ownership.

## 5. Dépendances

Les snapshots utilisent `AccountId` comme Value Object partagé du bounded
context, jamais comme FK. Mappers et repositories dépendent de PDO uniquement
en Infrastructure. Domain et contrats persistence ne dépendent ni de PDO ni de
Laravel.

## 6. Confidentialité

| Donnée | Forme durable |
|---|---|
| password | jamais stocké ; hash historique reste 042 |
| session/recovery/contact secret | hash |
| email/téléphone Profile/Claim | chiffré + fingerprint HMAC |
| login attempt identifier | fingerprint HMAC |
| device/network | clé pseudonymisée et bornée |
| revision old/new PII | enveloppe protégée/référence purgeable |

Les mappers redacted debug et ne sérialisent aucun secret par défaut.

## 7. Dépendances circulaires

Aucune table Profile ne référence Session/Closure et réciproquement. Les
identifiants corrélés sont des valeurs sans FK. La cohérence métier sera
orchestrée ultérieurement, jamais par trigger cross-owner.
