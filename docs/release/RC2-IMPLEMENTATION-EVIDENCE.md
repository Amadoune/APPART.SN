# RC2-01 — Implementation Evidence

## Modifications produit

| Fichier | Modification |
|---|---|
| `app/Http/Controllers/MediaAuthoringHttpController.php` | remplacement ciblé de `getRealPath()` par `getPathname()` ; diagnostic structuré des branches 503 |
| `tests/Architecture/MediaAuthoringPublicSurfaceArchitectureTest.php` | invariant empêchant la réintroduction de `getRealPath()` dans la surface upload |

## Preuve causale

1. appel direct Binary Storage : `applied` ;
2. appel direct Readiness : `applied` ;
3. appel direct du pipeline complet : `created` ;
4. rejeu HTTP avant correction : HTTP 503 ;
5. instrumentation HTTP : `uncaught_exception`, `ValueError`, `Path must not be empty`, ligne d'ouverture `getRealPath()` ;
6. rejeu HTTP après correction : HTTP 201 ;
7. stage navigateur `media-preview` atteint.

Les preuves navigateur RC2 externes sont conservées sous `RC2-01/validation-artifacts`. Elles ne font pas partie du produit.

## Frontières préservées

RC1 demeure le commit immuable `af7a61ea288ddc2177b508bd8c5bfb7c56ab1393`. Aucun changement ne touche IAM, Property, Listing Lifecycle, Publication Review, Search, Projection, SEO, P05, P08 ou le Release Evidence Runner.
