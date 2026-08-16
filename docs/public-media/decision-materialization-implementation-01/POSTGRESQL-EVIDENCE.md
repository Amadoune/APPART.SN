# PostgreSQL Evidence

Sur la base isolée `appart_test`, 6 tests PostgreSQL existants et nouveaux passent avec 30 assertions. Le scénario V2 couvre Applied, reader Found, version positive, locator exact, replay, asset revision change, nouvelle version monotone, retrait/archive et `SourceNotReady`.

Le store V1 historique conserve ses 5 tests PASS. Aucune donnée `appart_rebuild` n'a été utilisée par la campagne destructive.
