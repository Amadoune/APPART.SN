# Listing Revision Authority 01 — PostgreSQL Evidence

L'allocation ne possède aucun état PostgreSQL. Deux appels concurrents avec le même intent convergent mathématiquement ; deux intents distincts produisent des identités distinctes. L'optimistic locking et le rollback du Registry restent inchangés. Une campagne PostgreSQL n'est pas applicable à ce port pur.
