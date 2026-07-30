# Historical Redirect HTTP Integration Analysis

## Orchestration

L'adaptateur public conserve la priorité absolue de `PublicListingQuery`. Une projection Current est rendue immédiatement et aucun port historique n'est appelé. En son absence, le chemin public exact est encapsulé par `CanonicalUrl`, puis transmis à `HistoricalCanonicalQualifier`. Seul son résultat Historical fournit l'identité acceptée par `HistoricalRedirectResolver`.

Le contrôleur ne construit jamais `HistoricalCanonical`, ne consulte aucune donnée métier et ne suit aucune destination. Il mappe exhaustivement les statuts certifiés vers HTTP.

## Sécurité

La route reste limitée à `annonces/{slug}`. `CanonicalUrl` impose HTTPS et le domaine APPART.SN. La réponse de redirection reprend directement `HistoricalRedirectTarget::canonical->value`; aucune entrée de requête ne devient un en-tête `Location`. Les chemins de `ListingId` ne correspondent pas à la route.

## Fail-safe

Unknown, Current, NotFound et DestinationMissing rendent 404. Les ambiguïtés, corruptions, boucles, chaînes et indisponibilités rendent 503. Aucun de ces états ne contient `Location`. Les exceptions d'infrastructure ne sont jamais exposées.
