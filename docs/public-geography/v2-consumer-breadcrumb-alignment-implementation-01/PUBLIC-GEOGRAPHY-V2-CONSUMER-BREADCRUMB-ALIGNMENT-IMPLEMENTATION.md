# Public Geography V2 consumer breadcrumb alignment

Le contrat V2 est matérialisé comme une évolution additive. Une décision V2 porte `schemaVersion`, `terminalPlaceId`, `status`, `locality`, le breadcrumb root→leaf et le vecteur de révisions. Les items autoritatifs portent uniquement leur identité, type, nom officiel, parent et version; aucune URL ni slug.

Le mapper discrimine explicitement V1 historique et V2. Les consumers transportent le breadcrumb V2 séparément du breadcrumb ContentSeo V1 et du canonical Listing. Aucune matérialisation Public Geography, migration, projection RC2 ou règle métier n’a été ajoutée.
