# Sémantique des données Search attendues

| Donnée | Présente dans la décision | Owner normatif | Usage par la Projection actuelle |
|---|---:|---|---|
| état `visible/hidden/removed` | oui | SearchDiscovery | validé, non copié dans le read model |
| rang | oui | SearchDiscovery | validé, non copié |
| facettes | oui | SearchDiscovery, à partir de faits owners | validées, non copiées |
| révision Listing | oui | fait ListingLifecycle, composition SearchDiscovery | intégrité de la décision, non copiée |
| révision Property | oui | fait RealEstateCatalog, composition SearchDiscovery | intégrité de la décision, non copiée |
| révision Media | oui | fait Media, composition SearchDiscovery | intégrité de la décision, non copiée |
| version de décision | oui | SearchDiscovery | **copiée dans le watermark** |
| titre / description | non | ContentSeo | proviennent du snapshot Content/SEO |
| prix | non | aucun fait public démontré ici | non attendu |
| labels Geography | non | Public Geography | lus séparément |
| texte normalisé | non | non démontré | non attendu |
| éligibilité Publication | non | ListingLifecycle | observée via Aggregate Published |
| document Search public | non | Projection publique | construit en aval dans `listing_projections` |

La dénomination « Search source » masque donc une dépendance de **version de décision Search finale**, pas une dépendance au résultat Search public.
