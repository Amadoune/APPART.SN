# Idempotency Evidence

L’idempotence repose sur l’identité UUIDv5 stable, le `SourceRevisionSet`, la version de décision et le writer existant.

Preuve terminale RC2 :

1. premier catch-up : `applied` ;
2. replay avec les mêmes sources : `already_applied` ;
3. décision inchangée : UUID `20d5ab5a-45ac-5a5e-9f15-d1357e9105a9`, version `1`.

Le test PostgreSQL confirme également qu’une seule ligne existe après replay.
