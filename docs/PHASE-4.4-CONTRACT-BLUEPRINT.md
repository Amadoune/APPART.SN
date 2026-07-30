# Phase 4.4 — Lead Lifecycle Contract Blueprint

## Workflow cible

Types pressentis, à figer en 4.4A :

- `LeadLifecycleWorkflow` ;
- `LeadLifecycleState` : Created, Delivered, Rejected, Closed ;
- `LeadLifecycleAction` : Create, Deliver, Reject, Close ;
- `LeadLifecycleTransition` ;
- `LeadLifecycleDecision` et diagnostics fermés.

Transitions observées : Created → Delivered, Created → Rejected, Delivered → Closed, Rejected → Closed. La création doit être modélisée explicitement sans inventer un état implicite ; 4.4A décidera entre une commande de création séparée et un état initial fermé.

## Persistance

- `LeadLifecycleWorkflowStore` avec résultats de lecture/écriture fermés ;
- journal append-only dans `contacts_leads` ;
- version positive, continuité, idempotence, concurrence et déduplication ;
- aucune matrice métier PHP dans le repository ;
- contraintes PostgreSQL limitées aux transitions certifiées.

## Événement et transport

- catalogue événementiel bijectif transition → événement ;
- payload V1 minimal, immuable et sans donnée personnelle libre ;
- identité SHA-256 déterministe ;
- `LeadLifecycleDeliveryPayload` opaque et byte-for-byte ;
- `LeadLifecycleRoutingStatus` prévu dès 4.4F : Stored, AlreadyStored, CorruptedEnvelope, PersistenceCorrupted ;
- `LeadLifecycleEventRouterPort::route(envelope): LeadLifecycleRoutingResult`.

## Consommation

Matrice recommandée à certifier en 4.4G-R1 :

| Routage | Consommation générique |
|---|---|
| Stored | Consumed |
| AlreadyStored | AlreadyConsumed |
| CorruptedEnvelope | DivergentPayload |
| PersistenceCorrupted | RetryableFailure |

## Owners

- journal : `contacts_leads` ;
- Inbox : `contacts_leads` ;
- Outbox : mapping `ContactsLeads → contacts_leads` ;
- migration 005 inchangée ;
- migrations additives postérieures à la baseline actuelle 021.

## Atomicité et HTTP

L'intégrateur atomique reçoit tous les instants explicitement, exécute workflow/store/catalogue/Outbox dans une transaction unique et absorbe les erreurs techniques dans un résultat fermé. HTTP ne dépend que de l'intégrateur, valide le transport et possède un mapping exhaustif sans `default`.
