# Phase 5.1B — Authentication Contract

## 1. Owner et responsabilité

Owner unique : `IdentityAccess / Authentication`.

Authentication vérifie une preuve de credential, applique la politique
Attempts/Lockout et, en cas de succès, demande la création d'une Session. Il
ne possède ni Account, ni Profile, ni Session, ni Closure.

## 2. Version

Contrat logique `Authentication V1`. Toute modification des entrées,
résultats ou sémantiques exige V2. Les paramètres de policy portent un
`policyVersion` distinct.

## 3. Commands et Queries

### Authenticate

Entrées normatives :

- `authenticationIntentId` opaque et unique ;
- `loginIdentifier` traité comme secret transport ;
- `plainCredential` éphémère, jamais sérialisable ;
- `clientContext` minimisé : network risk key, user-agent class et device key
  non directement identifiants ;
- `requestedAt` explicite ;
- `policyVersion`.

### InspectAuthenticationAvailability

Query interne composée à partir de l'identité résolue, Account Status et
Closure. Elle ne révèle jamais ses détails au client public.

## 4. Résultats fermés

| Résultat interne | Sens | Résultat public futur |
|---|---|---|
| Authenticated | preuve valide, session command admissible | succès |
| InvalidCredentials | identité absente ou preuve invalide | échec générique |
| TemporarilyLocked | tentative bloquée par policy | échec générique |
| AccountUnavailable | Suspended ou Closed | échec générique |
| VerificationRequired | policy exige une vérification existante | échec générique |
| ReplayApplied | même intent déjà réussi | même résultat sûr |
| ReplayConflict | même intent, entrées divergentes | échec générique |
| Indeterminate | dépendance indisponible/incohérente | indisponible générique |

Aucune distinction publique ne permet de savoir si l'Account, le Profile ou
la claim existe.

## 5. Attempts / Lockout

Owner : `IdentityAccess / Authentication Attempts`.

États : `Allowed`, `TemporarilyLocked`.

Command `RecordAuthenticationOutcome` :

- clé composée d'un fingerprint HMAC versionné de l'identifiant normalisé et
  d'une risk key réseau ;
- outcome `Failure` ou `Success` ;
- instant et policy version ;
- idempotency key liée à l'intent.

Invariants :

1. aucun identifiant clair n'est persisté dans le compteur ;
2. un failure identique rejoué ne compte qu'une fois ;
3. un succès ne déverrouille que selon la policy versionnée ;
4. lockout n'est jamais Account Status ;
5. la durée est bornée et déterministe ;
6. un changement de policy n'altère pas rétroactivement une décision.

## 6. Credential boundary

Le futur port `CredentialVerifier` reçoit une identité technique résolue et le
secret éphémère ; il retourne seulement `Match`, `NoMatch` ou `Unavailable`.
Il n'expose jamais le hash.

Le futur `LoginIdentityResolver` retourne `Resolved(AccountId)` ou
`NotResolved`; ces résultats restent internes et ne modifient pas
`AccountRegistry`.

## 7. Idempotence et concurrence

- même intent + même checksum : résultat stable ;
- même intent + checksum différent : ReplayConflict ;
- la décision Attempts et la création Session devront être orchestrées sans
  double session ;
- deux succès concurrents restent soumis à la policy de sessions concurrentes ;
- un lockout observé ne peut être contourné par une course de création Session.

## 8. Confidentialité

- secret effacé de la mémoire applicative dès que possible ;
- aucun log/event/debug du secret, hash ou login clair ;
- timing et réponse publics homogènes ;
- diagnostics internes réservés à l'observabilité sécurisée ;
- AuthenticationSucceeded/Failed ne sont pas des événements publics V1.

## 9. Compatibilité

Lecture seulement de `AccountRegistry::find`, des queries Account existantes,
du Profile identity reader et d'Availability. Aucun changement Account,
Snapshot V1, 041–043, Account Status, Runtime 58, HTTP ou Outbox Status.
