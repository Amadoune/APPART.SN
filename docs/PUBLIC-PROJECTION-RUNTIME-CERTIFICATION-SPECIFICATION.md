# Public Projection Runtime Certification — spécification finale

## Chaîne certifiée

Mutation Aggregate → transaction Aggregate/Outbox → Outbox PostgreSQL → Worker Laravel → Consumer → Lookup → sources Runtime → Updater → Projection Store PostgreSQL → `PublicListingQuery` → HTTP.

## Invariants vérifiés

- commit et rollback Aggregate/Outbox atomiques ;
- message invisible avant commit et claimable après commit ;
- claim, lease, routing et acknowledgement durables ;
- ordre causal par Aggregate ;
- redelivery at-least-once et convergence idempotente ;
- pagination multi-target bornée et exhaustive ;
- une seule projection Current par Listing dans la génération Active ;
- aucune Candidate, Historical ou Tombstone servie comme Current ;
- HTTP sans lecture Aggregate ni recalcul Search/SEO/canonical ;
- Runtime Health `Healthy`.

Les scénarios HTTP 404/503, noindex, JSON-LD, corruption et indisponibilité Store restent portés par la certification HTTP réelle 3.6G et sont rejoués dans les validations complètes.
