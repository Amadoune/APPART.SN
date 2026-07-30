# Phase 4.9P-A — Historical Account Persistence Discovery and Audit

## Statut et mandat

```text
4.9C-R3 → NO GO CERTIFIÉ, FERMÉ
HistoricalAccountLookupV1 → ABANDONNÉ comme trajectoire principale
4.9D → SUSPENDU
4.9P-A → AUTORISÉ, OUVERT
```

4.9P-A est exclusivement documentaire. Il définit la future persistance
complète de l'agrégat historique `Account` sans modifier son comportement.

## Réponse centrale

La représentation durable minimale mais complète est un snapshot relationnel
versionné composé :

1. d'une racine Account portant identité, identités naturelles, nom, statut
   historique, version globale et chronologie;
2. d'un Credential courant;
3. de deux Verification, Email et Phone;
4. de l'historique des RoleAssignment;
5. de l'historique des Consent.

La reconstitution passe exclusivement par `Account::reconstitute()`. Elle
n'appelle aucune mutation métier et ne produit aucun événement.

Les événements en attente ne font pas partie du snapshot durable de
`AccountRegistry`. Le Repository ne les libère ni ne les publie. Un futur
contrat Outbox distinct serait nécessaire pour toute publication.

## Agrégat constaté

Le code établit que `Credential`, les deux `Verification`, les
`RoleAssignment` et les `Consent` sont actuellement possédés par `Account` :

- leur état est privé dans la racine;
- leurs mutations passent par la racine;
- ils sont clonés avec la racine;
- ils sont exigés par `Account::reconstitute()`;
- aucun port ou repository autonome n'existe.

Cette constatation de structure ne transfère aucune décision du domaine vers la
Persistance.

## Stratégie de création initiale

Aucune source legacy externe n'est présente ou configurée dans le dépôt.
Aucun dump, schéma, mapping de colonnes ou identifiant de provenance n'est
disponible. Une migration de données legacy ne peut donc pas être conçue.

La stratégie initiale réelle et démontrable est la création native :

```text
RegisterAccount
→ Account::register()
→ AccountRegistry::add()
```

`add()` persiste atomiquement la version 0 et toutes les données constitutives.
Une éventuelle importation legacy future exige un jalon séparé fondé sur une
source effectivement fournie, avec règles de conversion et de quarantaine.

## Frontière d'autorité

Historical Account Persistence possède :

- existence durable de l'agrégat historique;
- reconstitution fidèle;
- unicités `accountId`, email et téléphone;
- version optimiste globale `Account::version()`;
- atomicité de la racine et des enfants.

Elle ne possède pas :

- les transitions Account Status 4.9;
- le journal 041;
- sa version lifecycle;
- les décisions Suspend/Reactivate 4.9;
- la publication d'événements.

```text
Account global version ≠ Account Status lifecycle version
```

Le store 4.9C consulte `AccountRegistry` uniquement pour l'existence et
l'amorçage historique. Après bootstrap, le journal 041 reste l'autorité du
statut lifecycle.

## Gate de mapping découvert

La reconstitution est disponible, mais l'extraction complète ne l'est pas :

- `Account` n'expose ni `lastChangedAt`, ni Credential, ni collections;
- `PasswordHash` masque volontairement sa valeur encodée;
- `VerificationToken` masque volontairement son secret;
- `Verification` masque token, émission et expiration;
- `Consent` ne fournit pas `withdrawnAt`.

La réflexion, `serialize()`, le contournement de visibilité et les setters
opportunistes sont interdits. Le Sprint 4.9P-B devra donc définir un contrat
versionné de snapshot de persistance, à visibilité et usage bornés, capable
d'extraire et de restaurer les données sensibles sans les exposer aux couches
générales. Cette évolution devra être auditée comme amendement non
comportemental avant tout mapper.

Sans ce gate, aucune implémentation fidèle de `add()` et `save()` n'est
possible.

## Schéma futur pressenti

Le schéma propriétaire retenu est `identity_access`, déjà propriétaire du
journal 041, avec des tables Account distinctes. La migration future ne modifie
pas 041 et ne duplique pas ses transitions.

Tables pressenties, non créées pendant 4.9P-A :

- `identity_access.accounts`;
- `identity_access.account_credentials`;
- `identity_access.account_verifications`;
- `identity_access.account_role_assignments`;
- `identity_access.account_consents`.

Les noms, colonnes, clés et contraintes deviennent contractuels uniquement en
4.9P-B/4.9P-C.

## Résultats et exceptions

Le port existant reste normatif :

- `find()` retourne `Account|null`; un état durable invalide doit provoquer un
  rejet de persistance typé, jamais être transformé en `null`;
- `add()` réussit ou lève `DuplicateAccountIdentity`;
- `save()` réussit ou lève `ConcurrentAccountModification`.

Des diagnostics Infrastructure `Corrupted` ou `PersistenceRejected` peuvent
être internes, mais ne doivent pas modifier silencieusement le port certifié.

## Roadmap 4.9P

```text
4.9P-A Discovery and Aggregate Persistence Audit
→ 4.9P-B Persistence Contract and Mapping Foundation
→ 4.9P-C PostgreSQL Persistence Foundation
→ 4.9P-D Runtime Composition
→ 4.9P Final Certification
→ recertification ciblée 4.9C
→ reprise 4.9D
```

4.9P-B devra d'abord lever le gate d'extraction sécurisée. Tous les jalons
suivants restent fermés jusqu'à certification du précédent.

## Conclusion

Une stratégie complète est architecturalement possible et possède une
stratégie de création initiale réelle. Elle est conditionnée par un contrat
versionné de snapshot sécurisé; aucune infrastructure ne doit être écrite avant
sa certification.
