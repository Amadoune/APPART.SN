# Phase 5.1D — Divergence Policy

## Classification fermée

| Code | Exemple | Décision |
|---|---|---|
| `NormalizationDivergence` | normalisation non reproductible/version incompatible | interruption |
| `DuplicateHistoricalClaim` | deux snapshots convergent vers le même fingerprint | quarantaine |
| `IncompleteData` | nom/email/téléphone absent ou Snapshot non V1 | quarantaine |
| `ClaimCollision` | fingerprint déjà réservé par un autre AccountId | quarantaine |
| `ProfileConflict` | Profile préexistant hors du run | quarantaine |
| `ProtectionFailure` | protection/fingerprint impossible | interruption |

Une correction automatique n'est autorisée que si elle est entièrement
déterministe et couverte par la version de normalisation. Toute autre anomalie
est mise en quarantaine ou interrompt le run.

## Garanties

- la préqualification précède toute écriture Profile/Claim ;
- un run quarantiné conserve l'autorité `Historical` ;
- l'evidence est un SHA-256 sans PII ;
- aucune divergence n'est convertie en warning ;
- une résolution produit un nouveau run versionné ; elle ne réécrit pas
  silencieusement le rapport initial.
