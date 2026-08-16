# RC2 Draft Evidence

Cas obligatoire :

- PropertyId `438dfd4a-19a9-4c6f-a91a-aedac4a0d142` ;
- ListingId `965a44a3-780b-4d41-a7c6-e433abb26460`.

La lecture locale finale retourne zéro ligne pour :

- `real_estate_catalog_authoring.property_authoring` ;
- `listing_authoring.drafts` ;
- `listing_authoring.ownerships` ;
- `listing_authoring.portfolio_items` ;
- `listing_lifecycle.listings`.

Le draft exact n'est donc pas reprenable dans l'état local courant. Aucune donnée n'a été recréée et aucune mutation compensatoire n'a été effectuée.

Point d'exploitation important : `APPART_TEST_PG_DSN` et `.env DB_DATABASE` ciblent tous deux `appart_test`. La campagne PostgreSQL utilise `PostgreSqlTestEnvironment::reset()` ; elle n'était donc pas isolée des données locales de démonstration et a supprimé ces lignes. Ce défaut d'isolation de l'environnement de test empêche la preuve RC2 obligatoire.

## Recertification 01 — nouveau draft de preuve

L'ancien draft n'a pas été reconstruit. Après certification de l'isolation, un nouveau parcours productif a créé :

- PropertyId `5797a9b5-088d-43ea-8854-cac441946f6b` ;
- ListingId `979cd5aa-ced1-48a1-8adf-8b29c843a0c2` ;
- Property Authoring version 1 ;
- Listing Draft version 1 ;
- Geography Dakar City `c3120000-0000-4000-8000-000000000003` ;
- un média réel ingéré et rattaché.

Deux lectures HTTP successives, dont un reload, restaurent les mêmes IDs, versions, Geography, Media metadata et le step 6. Le fingerprint des 8 lignes autoritatives reste `84e240ea2048ca9782c312abc57916a7` avant/après Resume et après une campagne PostgreSQL sur `appart_test`.
