# Lead Lifecycle Outbox Owner Schema Analysis

## Décision

Le module propriétaire `ContactsLeads` est résolu exclusivement vers le schéma `contacts_leads`. La résolution inverse est utilisée par le Reader générique et le filtrage `source_module` empêche toute lecture depuis un mauvais owner.

Les Writer et Reader certifiés sont réutilisés sans duplication. Leur unique résolveur de schéma reçoit une septième correspondance additive. Aucun mapper événementiel, catalogue Lead, Consumer ou Worker n'est introduit.

## Compatibilité

Les quatre tables, leurs colonnes, contraintes et deux index reproduisent strictement la structure historique. La migration 005 et l'extension Reservation 021 restent gelées. La migration 026 ne référence que `contacts_leads`.

## Frontière

Cette fondation rend l'owner utilisable par le Writer et le Reader, mais ne produit aucun événement et n'écrit rien au bootstrap. Aucune intégration workflow–Outbox n'appartient au sprint.
