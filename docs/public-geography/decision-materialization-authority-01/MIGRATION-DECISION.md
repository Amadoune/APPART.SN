# Migration decision

**NO MIGRATION**. `public_geography.decisions` contient déjà identité, version, causalité, checksums, payload et timestamps avec contraintes adaptées.

La lacune est normative/applicative, pas structurelle. Si l'autorité préalable exigeait un nouveau store owner, ce serait un autre gate et non une migration de commodité ici.
