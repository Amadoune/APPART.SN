# P09 — Validation Report

## Preuves techniques acquises

| Gate | Résultat |
|---|---|
| Unit / Feature / Architecture P09 | PASS — 6 tests, 40 assertions |
| Régressions publiques ciblées | PASS — 12 tests, 75 assertions |
| PHPStan ciblé | PASS — 0 erreur |
| Pint ciblé | PASS |
| Vite | PASS |
| `git diff --check` | PASS |

Aucune campagne technique n'a été rejouée pendant la certification finale : aucun code n'a changé et les preuves terminales étaient déjà acquises.

## Démonstration navigateur terminale

### Home

- `https://appart.test/` chargé avec succès ;
- titre et description cohérents ;
- canonical `https://appart.test/` ;
- robots `index, follow` ;
- Open Graph et Twitter Card présents ;
- un H1.

### Recherche réelle

- critères : Acheter / Dakar / Appartement ;
- canonical filtré cohérent ;
- un résultat réel ;
- navigation vers `https://appart.test/annonces/p03-appartement-a-vendre-dakar`.

### Fiche réelle

- HTTP 200 ;
- canonical `https://appart.sn/annonces/p03-appartement-a-vendre-dakar` ;
- robots `index, follow` ;
- Open Graph avec image publique ;
- Twitter Card `summary_large_image` ;
- un bloc JSON-LD ;
- un H1.

### Sitemap et robots

- sitemap : Home, Recherche, P02 et P03 présents ;
- robots : `/` autorisé ; `/api/`, `/authoring/`, `/espace-proprietaire`, `/professional/` et `/_local/` exclus.

### Console et responsive

| Format | Largeur | Débordement | Résultat réel |
|---|---:|---|---|
| Desktop | 1440 px | aucun | présent |
| Tablette | 768 px | aucun | présent |
| Mobile | 390 px | aucun | présent |

Les journaux navigateur de Home, Recherche et Fiche sont vides : 0 erreur et 0 warning.

## Preuve visuelle

`docs/product/sprints/P09-PUBLIC-DISCOVERABILITY.png` consolide Home, Recherche avec un résultat réel et Fiche publique réelle.
