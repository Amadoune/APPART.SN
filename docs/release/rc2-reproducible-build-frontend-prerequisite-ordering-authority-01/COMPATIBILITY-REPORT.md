# Compatibility Report

| Surface | Décision |
|---|---|
| RC2-R2 | Immuable et préservée |
| Produit / vues / Feature tests | Aucun changement |
| Lockfiles | Inchangés |
| Workflow gates | Tous préservés, ordre frontend corrigé uniquement |
| Packaging | Réutilise le build réel de sa propre clean-room |
| RC2-R3 | Successor obligatoire |
| npm advisory | Gate sécurité/dépendances séparé |
| External CI / Production | Non ouvertes |

La décision renforce la reproductibilité sans bypass ni artifact synthétique.
