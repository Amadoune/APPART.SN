# Test matrix

## Unit

Identity, locator canonique, origin validation, delivery revision, revocation, no storageKey leak, V1 adapter.

## Feature

GET public; ready+attached+Published servi; unknown/unready/detached/removed/unpublished/stale revision → 404; infrastructure indisponible sans fuite.

## Architecture

Owner Media Public Delivery; aucune dépendance Projection; aucun path client; aucune migration.

## Security

Traversal, UUID/version invalides, IDOR, MIME spoofing, storageKey leakage, private/unready denied.
