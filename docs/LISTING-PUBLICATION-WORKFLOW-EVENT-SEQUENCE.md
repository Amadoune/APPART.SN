# Listing Publication Workflow Event Sequence

```mermaid
sequenceDiagram
    participant Caller
    participant Integration as Event Orchestrator
    participant Tx as PostgreSQL Transaction
    participant Workflow as Orchestrator 4.1D
    participant Catalog as Event Catalog 4.1EA
    participant Outbox as Existing Outbox

    Caller->>Integration: transition(request, metadata)
    Integration->>Tx: run(operation)
    Tx->>Workflow: transition(request)
    alt Denied or conflict
        Workflow-->>Tx: typed non-applied result
        Tx-->>Integration: same result, no Outbox write
    else Applied or AlreadyApplied
        Workflow-->>Tx: result + certified transition
        Tx->>Catalog: eventsFor(transition, version, metadata)
        Catalog-->>Tx: canonical ordered event
        Tx->>Outbox: append(delivery message)
        alt Applied or AlreadyApplied
            Outbox-->>Tx: accepted
            Tx-->>Integration: commit and original result
        else rejected or infrastructure failure
            Outbox-->>Tx: failure
            Tx-->>Integration: rollback and PersistenceFailure
        end
    end
```

Le Worker et le routeur sont volontairement absents de cette transaction : ils reprennent ultérieurement le message durable selon leurs contrats certifiés.
