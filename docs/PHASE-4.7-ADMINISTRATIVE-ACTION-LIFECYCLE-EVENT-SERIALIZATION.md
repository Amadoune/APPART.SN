# Phase 4.7E — Canonical Serialization

L'ordre canonique est `eventId`, `eventType`, `payloadVersion`, `payload`, `metadata`, `checksum`.

Le JSON utilise les options déterministes certifiées. Le checksum SHA-256 est calculé sur le JSON canonique sans son propre champ.

À données identiques, `eventId`, checksum et octets JSON sont identiques. Toute évolution de forme exige une nouvelle version.
