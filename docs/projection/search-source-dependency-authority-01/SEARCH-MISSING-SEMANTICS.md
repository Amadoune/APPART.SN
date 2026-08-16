# SearchMissing — sémantique exacte

| Élément | Preuve |
|---|---|
| Définition | `App\Application\ProjectionRuntimeSource\ProjectionSourceAssemblyStatus::SearchMissing` |
| Producteur | `CertifiedPublicListingProjectionSource::assemble()` |
| Condition | `SearchDecisionReader::readByListing()` retourne `SearchDecisionReadStatus::Missing` ou une décision nulle |
| Identité | `SearchDiscovery\Domain\ValueObject\ListingId`, construite depuis le ListingId public |
| Port interrogé | `Appart\Modules\SearchDiscovery\Application\Contract\SearchDecisionReader` |
| Binding | `SearchDecisionReader → PostgreSqlSearchDecisionReader` dans `PublicProjectionRuntimeServiceProvider` |
| Store | `search_discovery.public_search_decisions`, clé primaire `listing_id` |
| Consumer immédiat | `CertifiedPublicListingProjectionSource` |
| Réduction aval | `findByListingId()` retourne `null` → Updater `SourceUnavailable` → F3 `NotReady` |

La donnée manquante est un objet `SearchDecision` complet : `decisionId`, `listingId`, version positive et `SearchProjection` final. Toutefois, `PublicListingProjectionSources` ne conserve de cette décision que `decision->version` sous `searchVersion`. Le contenu état/rang/facettes/révisions est validé par le mapper mais n'alimente pas directement le read model public.

`SearchMissing` n'est pas produit par un binding absent : le reader exécute correctement une lecture ciblée et constate zéro ligne.
