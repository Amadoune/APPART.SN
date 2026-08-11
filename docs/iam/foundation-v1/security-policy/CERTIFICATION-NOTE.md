# IAM Security Policy Authority V1 — Certification Note

## Verdict

**GO PROPOSÉ — APPART.SN IAM FOUNDATION v1 — IAM SECURITY POLICY AUTHORITY v1**

## Portée du GO

F1 peut désormais être implémentée sans nouvelle décision de sécurité concernant identifier, credential hashing, secret Session, idle/absolute expiration, rotation, concurrence ou version de policy.

Les choix sont applicables uniformément en local, test et production, versionnés et compatibles avec une évolution V2. Les données historiques ne reçoivent aucune qualification rétroactive inventée.

## Garanties

- fail-closed et anti-énumération ;
- aucun plaintext persisté ;
- rotation atomique et ancien secret immédiatement invalide ;
- idle et absolute expiration serveur ;
- révocation/checkpoint, optimistic locking et replay ;
- cookie `__Host-` inchangé ;
- aucune modification de code, Provider, binding, migration, PostgreSQL, HTTPS ou portail ;
- F2 non ouverte et `FailClosedIdentityAccessHttpRuntime` inchangé ;
- aucun staging, commit ou tag.
