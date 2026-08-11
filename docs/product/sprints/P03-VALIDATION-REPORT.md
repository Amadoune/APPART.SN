# P03 — Validation Report

## Gates

| Validation | Résultat | Preuve |
|---|---|---|
| Unit / Feature / Architecture ciblés | PASS | 19 tests, 108 assertions |
| PHPStan ciblé | PASS | 0 erreur |
| Pint ciblé | PASS | format conforme |
| Vite | PASS | build production terminal |
| `git diff --check` | PASS | aucune erreur |
| PostgreSQL ciblé | NOT RUN | Reader non modifié pendant P03 |

## Validation produit réelle

- Home HTTP 200 et formulaire fonctionnel.
- Acheter envoie `transaction=sale`.
- Louer envoie `transaction=rent`.
- Ville et type sont transmis au Reader sans transformation silencieuse.
- Recherche `sale + Dakar + apartment` : un résultat PostgreSQL réel.
- Navigation vers la fiche canonique : HTTP 200.
- Louer : état vide visible et compréhensible.
- Paramètres inconnus ou invalides : validation en erreur, jamais ignorés.
- Console navigateur : aucune erreur.
- Desktop 1440 px : `scrollWidth = innerWidth = 1440`.
- Tablette 768 px : `scrollWidth = innerWidth = 768`.
- Mobile 390 px : `scrollWidth = innerWidth = 390`.
- Parcours principal réalisable sous 30 secondes.

La capture est disponible dans `P03-SEARCH-EXPERIENCE.png`.
