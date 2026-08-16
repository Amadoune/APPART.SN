# Manifest de preuve

Schéma fermé par étape :

```text
campaignId
step
timestamp
browserProfile
url
httpMethod
httpStatus
actorAccountId
propertyId
listingId
queueItemId
commandId
occurredAt
expectedVersion
resultVersion
applicationResult
postgresqlCheckpoint
evidenceReference
```

Les champs non applicables sont explicitement `null`. Aucun champ libre ne doit contenir credential, cookie, token CSRF ou secret.

Le manifest identifie les deux profils et les deux actors. Les preuves sont référencées par nom et checksum. Toute reprise après divergence utilise une nouvelle campagne ; aucun événement n'est silencieusement remplacé.
