# Administration Console Delivery Foundation — Delivery Matrix

| Catalogue | Event Status | Delivery Status | Type conservé |
|---|---|---|---|
| Operator | `Available` | `Available` | `administration_console.operator.observed.v1` |
| Operator | `Unavailable` | `Unavailable` | `administration_console.operator.observed.v1` |
| Operator | `Missing` | `Missing` | `administration_console.operator.observed.v1` |
| Operator | `Corrupted` | `Corrupted` | `administration_console.operator.observed.v1` |
| Operator | `DependencyUnavailable` | `DependencyUnavailable` | `administration_console.operator.observed.v1` |
| Queue | `Ready` | `Ready` | `administration_console.queue.observed.v1` |
| Queue | `Empty` | `Empty` | `administration_console.queue.observed.v1` |
| Queue | `Missing` | `Missing` | `administration_console.queue.observed.v1` |
| Queue | `Corrupted` | `Corrupted` | `administration_console.queue.observed.v1` |
| Queue | `DependencyUnavailable` | `DependencyUnavailable` | `administration_console.queue.observed.v1` |
| Audit | `Available` | `Available` | `administration_console.audit.observed.v1` |
| Audit | `Missing` | `Missing` | `administration_console.audit.observed.v1` |
| Audit | `Corrupted` | `Corrupted` | `administration_console.audit.observed.v1` |
| Audit | `DependencyUnavailable` | `DependencyUnavailable` | `administration_console.audit.observed.v1` |

`observedAt` est propagé sans transformation pour chaque ligne. La propagation est exhaustive, mécanique, bijective et homonyme, sans `default`, fallback, réduction ou décision.
