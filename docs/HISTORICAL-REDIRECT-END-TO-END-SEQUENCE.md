# End-to-End Historical Redirect Sequence

```text
Client GET /annonces/ancienne-canonical
  -> Laravel route
  -> PublicListingController
  -> PublicListingQuery: aucune projection Current
  -> HistoricalCanonicalQualifier
  -> PostgreSQL qualifications: Historical
  -> HistoricalCanonical fourni par le résultat
  -> HistoricalRedirectResolver
  -> PostgreSQL redirect decisions: Resolved(target)
  -> HTTP 301
  -> Location: target exact
```

La séquence s'arrête immédiatement après une projection Current. Elle s'arrête également sur tout statut autre que Historical ou Resolved. Le resolver n'est jamais rappelé sur la destination.
