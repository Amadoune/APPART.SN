# Intégration Submit

## Ordre certifié

1. Le Listing Draft owner-scoped fournit le `propertyId`.
2. La version Authoring attendue est transportée comme jeton de concurrence ; aucun fait Property n’est transporté.
3. `PromoteAuthoredPropertyV1` relit et promeut le snapshot.
4. Le Submit poursuit uniquement après `Applied` ou `AlreadyApplied`.
5. La transaction Listing effectue ensuite ses handoffs et la transition `Submitted` existante.

## Réduction des échecs

Owner/snapshot absent, incomplétude, conflit de version, divergence, rejet Domain et indisponibilité sont réduits vers les statuts Authoring existants. Aucun de ces résultats n’atteint la transition Listing.

La transaction Promotion précède la transaction Listing. Si Listing échoue ensuite, la Property compatible demeure et le retry réutilise l’idempotence F6, conformément au Blueprint.
