# Catch-up Model

Un catch-up recevable devra appeler le même matérialiseur que le chemin Published et réutiliser les mêmes readers owner, policy, identité, version, decision time et writer.

Il devra être ListingId-scoped, idempotent, sans SQL Application, fixture, constante RC2 ou reconstruction depuis Public Projection.

Ce modèle est techniquement possible mais non exécutable avant fermeture de la ContentSeo Snapshot Materialization Authority.
