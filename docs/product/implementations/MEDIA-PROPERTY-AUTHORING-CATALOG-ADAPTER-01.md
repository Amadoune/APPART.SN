# MEDIA PROPERTY AUTHORING CATALOG ADAPTER 01

## Périmètre

Cette implémentation permet au contrat Media `PropertyCatalog` de reconnaître un Property en préparation à partir du port read-only `PropertyAuthoringStore`. Elle ne crée aucun Aggregate, SQL, cache, mutation, HTTP ou nouvelle règle métier.

## Résolution

1. Media fournit uniquement le `PropertyId` typé au catalogue.
2. `PropertyAuthoringMediaCatalogAdapter` appelle `PropertyAuthoringStore::read(propertyId)`.
3. La résolution est positive uniquement pour un état portant le même `propertyId` et un `ownerAccountId` owner-scoped structurellement valide.
4. Si aucun état Authoring n'existe, le catalogue historique existant reste disponible via un fallback read-only séparé.

Le chemin Authoring est prioritaire et court-circuite le fallback : aucun Aggregate Property n'est relu pour un Property en préparation.

## Compatibilité

Les Property historiques restent reconnus par `RegistryMediaPropertyCatalog`. Aucun backfill, aucune migration et aucune duplication de données ne sont introduits.
