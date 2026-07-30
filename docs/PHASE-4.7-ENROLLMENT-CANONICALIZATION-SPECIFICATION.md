# Phase 4.7B-R2 — Enrollment Canonicalization Specification

La source canonique V1 est :

```text
administrative-action-lifecycle-enrollment-v1
{administrativeActionId}
{historicalVersion}
{lifecycleState}
```

Les séparateurs sont des LF, sans LF terminal. Les valeurs utilisent leur forme canonique certifiée. Le checksum du checkpoint est le SHA-256 hexadécimal minuscule de ces octets UTF-8.

Le checksum qualifie exclusivement la source Lifecycle nécessaire à l'enrôlement. Il ne prétend pas signer le texte du motif, les détails d'audit ou les autres données historiques, qui restent hors du journal Lifecycle.
