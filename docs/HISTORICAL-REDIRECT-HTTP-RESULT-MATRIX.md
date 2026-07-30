# Historical Redirect HTTP Result Matrix

| Étape | Résultat | HTTP | `Location` |
|---|---|---:|---|
| Current projection | trouvée | 200 | Non |
| Qualification | Current | 404 | Non |
| Qualification | Unknown | 404 | Non |
| Qualification | Ambiguous | 503 | Non |
| Qualification | Corrupted | 503 | Non |
| Resolver | Resolved | 301 | cible certifiée exacte |
| Resolver | NotFound | 404 | Non |
| Resolver | DestinationMissing | 404 | Non |
| Resolver | LoopDetected | 503 | Non |
| Resolver | ChainDetected | 503 | Non |
| Resolver | Ambiguous | 503 | Non |
| Resolver | Corrupted | 503 | Non |
| Qualifier ou resolver | exception d'infrastructure | 503 | Non |
