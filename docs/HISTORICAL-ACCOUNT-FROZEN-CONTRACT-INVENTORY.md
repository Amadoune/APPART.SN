# Historical Account — Frozen Contract Inventory

## Statut

Cet inventaire constitue la référence contractuelle gelée de la piste 4.9P.
Toute évolution d'un élément inventorié exige un amendement versionné, une
analyse d'impact et une nouvelle certification.

## Inventaire normatif

| Élément | Version / surface gelée | Responsabilité |
|---|---|---|
| `AccountRegistry` | `find(AccountId): ?Account`, `add(Account): void`, `save(Account, int): void` | Port applicatif unique de persistance de l'agrégat historique |
| `HistoricalAccountPersistenceSnapshotV1` | V1 | État complet de l'Account, version historique et collections enfants |
| `CredentialPersistenceSnapshotV1` | V1 | Hash encodé et instant de changement |
| `VerificationPersistenceSnapshotV1` | V1 | Canal, token, émission, expiration et vérification |
| `RoleAssignmentPersistenceSnapshotV1` | V1 | Historique ordonné des attributions et révocations |
| `ConsentPersistenceSnapshotV1` | V1 | Historique ordonné des consentements et retraits |
| `SensitivePersistenceValueV1` | V1 | Transport opaque des secrets dans la frontière autorisée |
| `AccountPersistenceMapper` | Contrat certifié 4.9P-B | Snapshot et réhydratation fidèle sans mutation métier |
| `PostgreSqlAccountRepository` | Implémentation certifiée 4.9P-C | Persistance PostgreSQL atomique et concurrent-safe |
| `042_historical_account_persistence` | Migration et rollback certifiés | Schéma durable Historical Account |
| bindings Runtime Historical Account | Composition certifiée 4.9P-D | Singleton mapper, singleton repository, alias unique `AccountRegistry` |

## Garanties gelées

- L'hydratation utilise les points de reconstitution dédiés; elle n'appelle
  aucune mutation métier et ne produit aucun événement artificiel.
- RoleAssignment et Consent sont conservés avec leurs ordinals et leur
  historique complet.
- Credential et les deux Verification sont obligatoires et reconstructibles.
- `find()` retourne `null` uniquement pour une racine absente; une donnée
  incomplète, invalide ou de version inconnue produit une corruption durable.
- Les secrets n'ont aucune conversion texte, refusent la sérialisation PHP et
  JSON, et restent redacted au debug.
- `revealForPersistence()` est borné au slice Historical Account autorisé :
  mapper et Repository PostgreSQL.
- Aucun setter opportuniste, mécanisme de réflexion, contournement de
  visibilité ou sérialisation générique n'appartient à la fondation.

## Exclusions gelées

Ces contrats n'exposent aucun HTTP, Event, Outbox, projection, log, Runtime
Health, Workflow Account Status ou décision de lifecycle. Ils ne constituent
pas un mécanisme d'import legacy.
