# Final Failure Matrix

| Situation | Résultat materializer | Effet Projection |
|---|---|---|
| Listing absent | SourceMissing | NotReady |
| Listing non Published mais état valide | Applied/AlreadyApplied avec état Hidden ou Removed selon policy existante | replay après décision autorisé, jamais Visible implicitement |
| Listing corrompu | SourceCorrupted | NotReady |
| Property absente | SourceMissing | NotReady |
| Property ou promotion corrompue/incompatible | SourceCorrupted | NotReady |
| Media absente | SourceMissing | NotReady |
| Media corrompue | SourceCorrupted | NotReady |
| révision nulle, non positive ou identité invalide | SourceCorrupted | NotReady |
| visibilité non résolue | SourceCorrupted | NotReady |
| ranking policy invalide | SourceCorrupted | NotReady |
| Writer indisponible | DependencyUnavailable | NotReady |
| candidate dominée | RejectedObsolete | décision courante conservée ; replay possible si Found |
| candidate incomparable ou même version divergente | Divergent | NotReady pour cette intention |
| contenu identique | AlreadyApplied | Reader Found, Projection rejouable |
| contenu nouveau légitime | Applied | Reader Found, Projection rejouable |

Aucun fallback vers Public Projection ou UI.
