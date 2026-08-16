# RC2 Post-Immutable-Materialization Sequencing Authority 01

## Decision

The current RC2 candidate is immutable and valid, but the repository’s Release build authorities still require R5. Packaging RC2 with them would fail identity checks or mislabel the source.

Exactly one next gate is authorized for later opening: **RC2 BUILD/CI SOURCE IDENTITY ALIGNMENT 01**.

No alignment, Git mutation, packaging, push, CI, Phase 5.9 reopening or deployment occurs here.
