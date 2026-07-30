# Search Decision Reader

## Ownership

SearchDiscovery reste propriétaire de la décision finale. Le store spécialisé n’est ni un moteur Search ni un Aggregate Repository. Il conserve seulement une copie durable et lisible de la décision produite.

## Lecture

`SearchDecisionReader::readByListing(listingId)` effectue une lecture ciblée et retourne :

- `Found` avec la décision exacte ;
- `Missing` lorsqu’aucune décision n’est persistée ;
- `Corrupted` lorsque le payload ou son checksum ne peut pas être validé.

La lecture ne consulte aucune horloge, ne complète aucune facet et ne recalcule aucun rank.

## Écriture

`SearchDecisionWriter` permet au propriétaire de publier la décision finale durablement. Les résultats sont `Applied`, `AlreadyApplied`, `RejectedObsolete` et `Divergent`.

Le writer rejoint une transaction externe lorsqu’elle existe. En transaction locale, il commit uniquement après l’écriture complète et rollback sur toute exception.
