# SecurityCompliance Outbox Foundation

L'Outbox appartient exclusivement à l'owner `SecurityCompliance` et reçoit uniquement les cinq Deliveries V1 certifiées : Secret Inventory, Security Audit, Incident, Privacy Policy et Compliance Control.

L'identité est déterministe : `eventId` couvre owner, type et observedAt ; `messageId` couvre owner et eventId. Le checksum SHA-256 versionné couvre owner, schemaVersion, eventId, type, statut et observedAt. Une identité identique avec le même contenu converge vers `AlreadyApplied`; avec un statut différent, elle produit `DivergentMessage`.

Le journal PostgreSQL est append-only et séparé de l'état technique mutable. Le claim utilise `FOR UPDATE SKIP LOCKED`; le retry est borné à dix tentatives. Les transactions externes, savepoints locaux et rollbacks externes sont préservés.
