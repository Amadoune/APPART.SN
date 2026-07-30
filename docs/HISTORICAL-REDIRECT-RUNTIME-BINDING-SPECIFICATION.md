# Historical Redirect Runtime Binding Specification

La composition normative est :

```text
HistoricalRedirectResolver
  -> PostgreSqlHistoricalRedirectResolver (singleton)
       -> PDO PostgreSQL Runtime (singleton existant)
       -> HistoricalRedirectDecisionMapper (singleton)
```

Le contrat est enregistré par alias afin que sa résolution et celle de la classe concrète retournent strictement la même instance. Aucun double enregistrement, Fake, Null Object ou fallback n'est admis.

Les enregistrements restent paresseux. Le bootstrap ne doit appeler ni `make` sur ce graphe, ni `resolve`, ni une opération SQL. Runtime Health peut construire l'adaptateur pour vérifier sa compatibilité, sans exécuter de résolution historique.
