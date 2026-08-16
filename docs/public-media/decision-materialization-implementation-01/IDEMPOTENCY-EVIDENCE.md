# Idempotency Evidence

Le replay strict RC2 retourne `AlreadyApplied`, avec la même collection, la même version 1 et le même item. Le test PostgreSQL confirme qu'une seule ligne Public Media demeure après replay.
