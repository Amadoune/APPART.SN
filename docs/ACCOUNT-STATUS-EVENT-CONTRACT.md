# Account Status — Event Contract V1

## Catalogue fermé

```text
account.status.suspended
account.status.reactivated
```

| Transition | Event |
|---|---|
| `Active → Suspend → Suspended` | `account.status.suspended` |
| `Suspended → Reactivate → Active` | `account.status.reactivated` |

Toute autre transition est rejetée.

## Enveloppe V1

```text
eventId
eventType
payloadVersion = 1
occurredAt
payload
```

Le payload contient exclusivement :

```text
accountId
previousState
action
currentState
occurredVersion
```

L'identité SHA-256 est déterministe à partir du type, de la version du payload,
de l'Account, de la transition et de la version lifecycle résultante.

## Confidentialité

Le contrat n'expose aucun actorId, intentId, version attendue/observée, version
Historical Account, Credential, token de Verification, rôle, Consent ou secret.
Il publie exclusivement le fait lifecycle.

## Versionnement

V1 est la seule version autorisée. Toute modification de forme ou de
sémantique exige une nouvelle version contractuelle et une certification.

## Frontière

Le catalogue construit un contrat en mémoire. Il ne publie rien et n'introduit
aucun Transport, Routing, Inbox, Outbox, Delivery, Worker ou HTTP. Il n'est pas
connecté à l'orchestrateur pendant 4.9F.
