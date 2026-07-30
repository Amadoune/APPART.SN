# Phase 4.9P-B — Historical Account Persistence Contract and Mapping Certification

## Gate R1

```text
4.9P-B-R1 — Secure Persistence Snapshot Boundary V1
→ SATISFAIT
```

La frontière V1 est explicite, non événementielle, sans réflexion, sans
sérialisation générique et sans setter. Les secrets sont reconstructibles,
redacted au debug et non convertibles en texte.

## Livrables

- `HistoricalAccountPersistenceSnapshotV1`;
- `CredentialPersistenceSnapshotV1`;
- `VerificationPersistenceSnapshotV1`;
- `RoleAssignmentPersistenceSnapshotV1`;
- `ConsentPersistenceSnapshotV1`;
- `SensitivePersistenceValueV1`;
- `AccountPersistenceMapper`;
- documentation Boundary, Mapping et Invariants;
- tests unitaires et Architecture.

## Matrice GO

| Critère | Résultat |
|---|---|
| snapshot V1 complet | SATISFAIT |
| secrets extractibles et bornés | SATISFAIT |
| hydratation non événementielle | SATISFAIT |
| aucun setter | SATISFAIT |
| aucune réflexion | SATISFAIT |
| aucune sérialisation générique | SATISFAIT |
| historiques enfants conservés | SATISFAIT |
| invariants fermés | SATISFAIT |
| round-trip démontré | SATISFAIT |
| mapper pur | SATISFAIT |
| AccountRegistry inchangé | SATISFAIT |
| aucune infrastructure PostgreSQL | SATISFAIT |

## Validations finales

```text
Snapshot / Mapper / Architecture ciblés
15 tests
86 assertions
PASS

IdentityAccess ciblé
75 tests
259 assertions
PASS

Architecture complète
555 tests
43 330 assertions
PASS

Suite complète
2 646 tests
51 042 assertions
PASS

Pint
PASS

PHPStan complet
0 erreur
```

Aucune campagne PostgreSQL n'est revendiquée : le sprint ne contient ni SQL,
ni migration, ni Repository PostgreSQL.

## Frontière préservée

Aucune migration, table, requête SQL, connexion PDO, transaction, binding,
Runtime Health, Event, Outbox ou HTTP n'est introduit. Le journal 041 et le
store Account Status restent inchangés.

## Verdict certifié

```text
4.9P-B
→ GO CERTIFIÉ
→ FERMÉ

4.9P-C
→ OUVERT

4.9D
→ RESTE SUSPENDU
```
