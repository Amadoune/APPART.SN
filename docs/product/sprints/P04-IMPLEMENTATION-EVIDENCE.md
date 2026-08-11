# P04 — Implementation Evidence

## Frontière consommée

| UI | Route | Contrat/read model | Source |
|---|---|---|---|
| Fiche premium | `GET /annonces/{slug}` | `PublicListingQuery` / `PublicListingReadModel` | projection publique certifiée |

Le contrôleur public existant demeure l'adaptateur HTTP. La vue ne réalise aucun accès à Authoring, `ListingDraft`, `ListingRegistry`, Aggregate, PostgreSQL ou Infrastructure.

## Changements

- `resources/views/public-listing.blade.php` : composition premium, rendu conditionnel des données publiques, accessibilité et CTA désactivé ;
- `resources/css/app.css` : styles P04 dans le Design Language existant et responsive ;
- `tests/Feature/PremiumPublicListingPageTest.php` : preuve du rendu public et du comportement historique non qualifié ;
- `tests/Architecture/PremiumPublicListingPageArchitectureTest.php` : preuve d'absence de contournement et de contact simulé.

Pour un média absolu du même hôte, la vue aligne uniquement le schéma et l'hôte sur la requête courante. Cette normalisation de présentation permet au média public local certifié d'être chargé sur `http://appart.test` sans modifier sa donnée ni sa source.

## Preuve visuelle

La capture desktop finale est `docs/product/sprints/P04-PROPERTY-PAGE.png`.
