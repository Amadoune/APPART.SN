# Idempotency Model

L’idempotence combine : snapshotId UUIDv5 stable, coherenceId déterministe, payload canonique, version dérivée des révisions et checksum du writer.

Même Listing, policy et sources produit la même identité, la même version et le même checksum : `AlreadyApplied`. Un catch-up et un handoff normal convergent donc vers le même snapshot.
