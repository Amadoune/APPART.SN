# Historical Redirect Specification

## Port applicatif

```php
interface HistoricalRedirectResolver
{
    public function resolve(HistoricalCanonical $canonical): HistoricalRedirectResolution;
}
```

Le port est propriétaire de `ContentSeo`. `HistoricalCanonical` est une identité déjà déclarée historique. `HistoricalRedirectTarget` est exclusivement une canonical publique. `HistoricalRedirectResolution` est immuable, final et construit uniquement par sept fabriques nommées.

## Contrat déterministe

Pour une même décision stockée et une même source, un adaptateur futur doit retourner le même statut, la même destination éventuelle et le même diagnostic. Il ne peut suivre une destination, consulter `PublicListingQuery`, lire un Aggregate, choisir parmi plusieurs lignes ou régénérer une canonical.

## Préconditions d'un futur adaptateur

L'adaptateur 3.10B devra lire une décision de redirection déjà matérialisée. Il devra classifier explicitement toutes les cardinalités et incohérences dans les sept statuts, sans branche `default`. Cette spécification n'autorise aucune infrastructure avant le GO 3.10A.

## Compatibilité

`PublicListingQuery::findByCanonicalPath(string)` reste inchangé et Current-only. Un futur adaptateur HTTP appellera d'abord ce contrat spécialisé uniquement selon l'orchestration qui sera spécifiée dans un sprint ultérieur ; aucune destination ne sera déduite dans le Web.
