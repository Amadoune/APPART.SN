# Listing Publication Runtime Sequence

```text
Caller
  -> ListingPublicationOrchestrator.transition(request)
      -> ListingPublicationWorkflowStore.read(listingId)
      <- stored state + version
      -> ListingPublicationWorkflow.decide(state, action)
      <- Allowed(transition) | Denied(diagnostic)
      -> ListingPublicationWorkflowStore.append(listingId, transition, version + 1) [Allowed uniquement]
      <- persistence result
  <- typed orchestration result
```

Le chemin `Denied` s'arrête avant toute écriture. Aucun chemin ne publie d'événement et aucun composant n'est appelé récursivement.
