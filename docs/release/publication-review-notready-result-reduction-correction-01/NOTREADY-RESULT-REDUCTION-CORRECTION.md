# NotReady Result-Reduction Correction 01

## Scope

The correction is confined to Publication Review HTTP/UI adaptation. The Application result catalog, Projection policy, publication workflow and every source authority remain unchanged.

`NotReady` now selects the `not-ready` presentation mode. `Applied` and `AlreadyApplied` retain the certified `confirmed` mode.

## Decision

The existing HTTP contract is preserved: the rendered response remains HTTP 200 and carries the existing closed `PublicationReviewExperienceStatus::NotReady` result. No authority supports changing that status code in this gate.
