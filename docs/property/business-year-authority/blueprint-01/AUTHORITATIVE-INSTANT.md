# Authoritative Instant

L'instant unique est `occurredAt` de la commande métier Property : première promotion pour Register, commande de modification pour Update.

Il est créé une fois par l'orchestration autorisée, puis conservé à l'identique lors des retries. L'instant de draft, l'heure de traitement, l'heure de persistance et le moment du replay ne sont pas utilisés.

Pour `PromoteAuthoredPropertyV1`, il s'agit du `occurredAt` déjà inclus dans le contrat et son checksum canonique.
