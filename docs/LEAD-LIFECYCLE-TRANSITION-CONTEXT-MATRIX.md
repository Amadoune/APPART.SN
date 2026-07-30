# Lead Lifecycle Transition Context Matrix

| Champ | Source | Obligatoire | Implicite | Rôle |
|---|---|---:|---:|---|
| `actor` | appelant | oui | interdit | audit |
| `occurredAt` UTC | appelant | oui | interdit | audit |
| `expectedVersion` | appelant | oui | interdit | concurrence |
| `nextVersion` | contrat | oui | `expectedVersion + 1` | ordre |
