# P04 — Validation Report

## Campagnes

| Gate | Résultat |
|---|---|
| Unit + Feature + Architecture ciblés | PASS — 15 tests, 85 assertions |
| Responsive navigateur | PASS — 1440, 768 et 390 px, aucun débordement horizontal |
| Média public réel | PASS — image chargée, dimensions naturelles non nulles |
| Accessibilité structurelle | PASS — H1 unique, headings, alt contextuel, contrôles natifs et focus conservé |
| Contact | PASS — CTA désactivé, aucune adresse, aucun téléphone, aucun lien de messagerie |
| PHPStan ciblé | PASS — 0 erreur |
| Pint ciblé | PASS |
| Vite | PASS |
| `git diff --check` | PASS |

## Données observées

- URL : `http://appart.test/annonces/p03-appartement-a-vendre-dakar` ;
- transaction : vente ;
- type : appartement ;
- ville : Dakar ;
- média : `/p02-first-listing.svg` fourni par la projection publique ;
- prix : absent de la surface publique, rendu « Prix non communiqué ».

Toutes les campagnes autorisées ont produit un résultat terminal PASS.
