# Test matrix

- Unit : affected terminal self/child/deep; scope existing-only; pagination/cursor; fanout identity; rename; enable/disable; merge; unavailable; replay; ordering/concurrency.
- PostgreSQL : vector lookup; parent/ancestor rename; Applied/AlreadyApplied; stale/divergent; Available↔Unavailable; pagination déterministe.
- Architecture : Geography/Public Geography owners; outbox existante; no Projection/Media/distributed transaction/migration.
- Integration : Geography event → affected terminals → V2 refresh → reader updated.
