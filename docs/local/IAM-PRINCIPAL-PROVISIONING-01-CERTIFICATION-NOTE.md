# IAM Principal Provisioning 01 — Certification Note

## Verdict

**NO GO PROPOSÉ**

Un use case `RegisterAccount` existe, mais il ne constitue pas à lui seul un provisioning local authentifiable. Il manque une autorité certifiée transformant un credential local en `PasswordHash`, une surface de provisioning locale qui la compose, et l'implémentation exécutable du port HTTP Login/Session actuellement lié au fallback fail-closed.

La création d'un seed, d'un hash ou d'une session ad hoc aurait contourné IAM et violé les interdictions. Aucun principal artificiel n'a été créé.

## Amendement requis, non ouvert

Une décision d'autorité distincte doit qualifier une chaîne IAM locale complète :

1. hashing de credentials owner-scoped ;
2. provisioning par `RegisterAccount` sans SQL direct ;
3. adapter réel `IdentityAccessHttpRuntime` pour Login et Session Inspection ;
4. preuves de cookie Secure et reload sur HTTPS.

**NO GO PROPOSÉ — APPART.TEST LOCAL IAM PRINCIPAL PROVISIONING 01**
