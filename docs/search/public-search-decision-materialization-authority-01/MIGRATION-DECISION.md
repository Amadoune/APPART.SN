# Migration Decision

`search_discovery.public_search_decisions` existe et porte déjà : ListingId, decisionId, version positive, état, payload canonique, checksum et timestamp.

Pour le writer spécialisé existant : **NO MIGRATION** est techniquement suffisant.

Cette conclusion n'autorise pas l'Implementation. Si un futur journal/outbox Search est retenu, sa persistance devra être justifiée par une authority distincte; elle ne peut être ajoutée par commodité dans ce chantier.

## Completion 01

Décision finale : **NO MIGRATION**. La table, le Reader, le Writer, le payload canonique et le checksum existants suffisent à Implementation 01.
