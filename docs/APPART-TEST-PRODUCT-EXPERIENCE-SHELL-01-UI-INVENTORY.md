# APPART.TEST Product Experience Shell 01 — UI Inventory

## Existant audité

| Surface | État initial | Décision |
|---|---|---|
| `resources/views/authoring-workspace.blade.php` | Vue fonctionnelle certifiée, Tailwind | Conservée sans modification |
| `resources/views/public-listing.blade.php` | Vue métier publique minimale | Conservée sans modification |
| `resources/views/local-bootstrap.blade.php` | Diagnostic local | Conservé et déplacé sur `/_local/bootstrap` |
| `resources/css/app.css` | Tailwind et font Instrument Sans uniquement | Étendu avec les primitives du shell |
| `resources/js/app.js` | Charge le parcours authoring | Conservé et étendu par un module UI isolé |
| `resources/js/authoring.js` | Comportement métier existant | Conservé sans modification |
| Vite/Tailwind | Opérationnels | Réutilisés |
| `public/build` | Assets compilés | Régénérés par Vite |
| Layouts/components Blade | Absents | Créés pour le shell public |
| Images produit | Absentes | Aucun asset legacy importé ; illustrations CSS locales |

## Réutilisation

Le pipeline Vite, Tailwind 4, Instrument Sans, la structure Laravel et les routes existantes sont réutilisés. Aucun composant du Domaine, aucun contrat et aucune donnée métier ne sont consommés par la nouvelle racine.
