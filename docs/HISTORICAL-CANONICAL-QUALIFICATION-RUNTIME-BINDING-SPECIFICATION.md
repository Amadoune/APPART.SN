# Historical Canonical Qualification Runtime Binding Specification

```text
HistoricalCanonicalQualifier
  -> PostgreSqlHistoricalCanonicalQualifier (singleton)
       -> PDO PostgreSQL Runtime (singleton existant)
       -> HistoricalCanonicalQualificationMapper (singleton)
```

Le port est enregistré par alias : résoudre le contrat ou la classe concrète retourne strictement la même instance. Le mapper est également unique. Aucun Fake, Null Object, fallback ou second provider n'est admis.

Le bootstrap enregistre uniquement le graphe. Runtime Health peut le construire pour vérifier le contrat, mais ne doit jamais appeler `qualify()`.
