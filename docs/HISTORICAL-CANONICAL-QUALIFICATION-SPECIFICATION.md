# Historical Canonical Qualification Specification

```php
interface HistoricalCanonicalQualifier
{
    public function qualify(CanonicalUrl $canonical): HistoricalCanonicalQualification;
}
```

`CanonicalUrl` garantit que l'entrée est une URL publique normalisée appartenant à APPART.SN. La qualification est immuable et ne peut être construite que par cinq fabriques nommées.

- `Current` : identité courante, aucun `HistoricalCanonical`.
- `Historical` : identité historique certifiée et convertible en `HistoricalCanonical`.
- `Unknown` : aucune qualification connue, diagnostic typé.
- `Ambiguous` : qualifications incompatibles, aucun choix implicite.
- `Corrupted` : décision non fiable ou illisible.

Les résultats Current et Historical ne portent aucun diagnostic d'anomalie. Les autres résultats portent exactement le diagnostic correspondant et n'exposent jamais d'identité historique.
