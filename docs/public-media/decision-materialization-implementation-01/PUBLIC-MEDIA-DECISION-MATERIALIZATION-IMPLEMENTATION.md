# Public Media Decision Materialization Implementation 01

## Résultat

`MaterializePublicMediaDecisionV2(ListingId)` assemble exclusivement les faits owner-side Listing et Media, puis réutilise le writer monotone Public Media existant. `CatchUpPublicMediaDecisionV2` est un alias du même matérialiseur.

Le payload V2 est additif et discriminé par `schemaVersion: 2`. La lecture V1 historique demeure supportée. Aucune migration, Projection, Search, ContentSeo ou Geography write n'est introduite.

Les handoffs `ListingPublished` et Media lifecycle délèguent au même matérialiseur. Ils ne construisent aucun item.
