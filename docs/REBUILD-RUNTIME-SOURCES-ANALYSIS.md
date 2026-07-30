# Sprint 3.7D — Analyse Rebuild Runtime Sources

## Décision d'énumération

Full et Range lisent `listing_lifecycle.listings.id` par keyset pagination. La clé primaire existante garantit l'ordre et sert les bornes ; aucune migration ni index supplémentaire n'est nécessaire. La requête lit au plus `limit + 1` identités et ne charge jamais l'ensemble global.

Listings utilise exclusivement l'ensemble explicite porté par le scope certifié. Les doublons sont déjà supprimés par `PublicProjectionRebuildScope`; l'adaptateur ordonne les identités lexicalement et ne consulte pas la base pour en ajouter ou en retirer.

## Décision de fabrication

`CertifiedPublicProjectionCandidateFactory` consomme l'inspection 3.7C. Une source bloquée ou un watermark non Ready ne produit aucun record. Pour une source valide, la factory délègue aux builders et à la policy déjà utilisés par l'Updater certifié, puis construit un `PublicListingProjectionRecord` pour la génération Candidate demandée.

La factory ne réimplémente aucune règle Search, SEO, canonical, Geography ou Media. Elle ne persiste rien ; seul le Rebuilder appelle le Writer certifié.

## Observabilité

Le contrat historique retourne `null` pour une Candidate indisponible. L'extension `inspect()` distingue source bloquée, promotion non prête, transformation certifiée rejetée, projection indisponible et record construit.

## Périmètre

Aucun Rebuilder, Store, Writer, Domain, Runtime, Laravel, HTTP ou SQL certifié n'est modifié.
