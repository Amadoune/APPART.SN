# Phase 5.1D — Seed Blueprint

## Autorités

Le Seed lit exclusivement des `HistoricalAccountPersistenceSnapshotV1`. Il ne
reçoit ni table historique mutable, ni `Account` comme source canonique, et
n'écrit jamais dans `identity_access`.

| Élément | Owner | Droit 5.1D |
|---|---|---|
| Snapshot V1 | Historical Account | lecture |
| User Profile | User Profile | création seed |
| Identity Claim | Identity Claim Registry | réservation/activation seed |
| manifest/quarantaine/run | Profile Claims Cutover | lecture/écriture |
| registre d'autorité | Profile Claims Cutover | bascule atomique |

## Pipeline déterministe

1. Trier les snapshots par `AccountId`.
2. Exiger Snapshot version 1.
3. Normaliser par `iam-profile-v1`.
4. Protéger les valeurs et calculer les fingerprints HMAC.
5. Dériver les Claim IDs et Intent IDs en UUID v5.
6. Calculer le checksum global sans secret en clair.
7. Préqualifier toutes les divergences.
8. En présence d'une divergence : écrire uniquement run + quarantaine.
9. Sinon : créer Profiles, Claims et manifest, puis basculer l'autorité dans
   une transaction PostgreSQL unique.

Un rejeu du même `run_id` et du même checksum retourne
`IdempotentReplay`. Tout payload différent sous le même identifiant est rejeté.

## Confidentialité

Les emails, téléphones et noms ne sont stockés que sous forme protégée. Les
rapports, manifests et quarantaines ne portent que des identifiants techniques
et des checksums. Aucun credential ou token historique n'est lu par le Seed.

## Frontières

La migration 052 est additive. Elle ne possède aucune FK cross-domain, aucune
cascade et ne modifie ni 041–043 ni 044–051. Aucun Runtime, Provider, HTTP,
Event, Delivery ou Outbox n'est introduit.
