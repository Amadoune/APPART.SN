# Listing Publication HTTP API Error Contract

## Erreurs de transport

Laravel retourne HTTP 422 avec le contrat de validation JSON existant pour les champs absents, les types invalides, une action non certifiée, une version non positive ou des métadonnées temporelles invalides. Aucun diagnostic métier n'est inventé.

## Résultats applicatifs

Les erreurs applicatives utilisent une enveloppe stable :

```json
{
  "status": "concurrency_conflict",
  "diagnostic": "version_conflict"
}
```

`diagnostic` est la valeur exacte du diagnostic certifié. Il est `null` pour les deux succès. Aucun message PostgreSQL, détail d'exception ou contenu Outbox n'est exposé.
