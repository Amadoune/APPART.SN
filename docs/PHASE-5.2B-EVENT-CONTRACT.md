# Phase 5.2B — Event Contract

## Catalogue V1 minimal

| Type | Condition |
|---|---|
| `media.ingestion.asset.ready.v1` | Asset devient Ready une seule fois |
| `media.ingestion.asset.rejected.v1` | rejet terminal |
| `media.ingestion.asset.purged.v1` | purge physique confirmée |

## Enveloppe

Version, eventId déterministe, owner, aggregateId opaque, aggregateVersion,
occurredAt, type et checksum canonique.

## Payload

IDs opaques, disposition fermée, recipeVersion et métadonnées techniques
minimales. Sont interdits : binaire, filename, URL, object key, chemin, PII,
token, diagnostic antivirus et contenu EXIF.

## Idempotence et replay

L’identité dérive de owner/type/aggregate/version. Même identité et même
checksum converge ; checksum différent est divergent et quarantiné. Retry est
borné. Aucun de ces événements ne remplace ni n’étend les Events V1 F-06.

## Décision

Le rattachement synchrone peut consommer directement `GetAssetReadinessV1`.
L’Event Ready sert aux consumers asynchrones ; il ne donne pas autorité pour
écrire dans F-06.
