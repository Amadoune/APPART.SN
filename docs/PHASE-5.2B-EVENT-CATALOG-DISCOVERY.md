# Phase 5.2B — Event Catalog Discovery

## Événements candidats minimaux

| Événement candidat | Owner | Consumers candidats |
|---|---|---|
| `media.ingestion.asset.ready.v1` | MediaIngestion.Asset | handoff Media |
| `media.ingestion.asset.rejected.v1` | MediaIngestion.Asset | UI privée, Audit futur |
| `media.ingestion.asset.purged.v1` | MediaIngestion.Asset | reconciliation |

Ce catalogue n’autorise aucune classe Event pendant Discovery. Contracts
Foundation devra démontrer si chacun est nécessaire ; un résultat synchrone ou
une lecture owner-scoped est préféré lorsqu’il suffit.

## Confidentialité

Les payloads futurs excluent octets, URL signée, object key, nom de fichier,
PII, diagnostic antivirus et secret. Ils peuvent contenir des IDs opaques,
versions de contrat, disposition fermée, timestamp et checksum canonique non
réversible.

## Compatibilité

Les événements F-06 et leurs payloads V1 restent inchangés. Aucun événement
Ingestion ne peut simuler `MediaAdded`, `Removed`, `Archived`, `Reordered`,
`MarkedPrimary` ou `CaptionChanged`.
