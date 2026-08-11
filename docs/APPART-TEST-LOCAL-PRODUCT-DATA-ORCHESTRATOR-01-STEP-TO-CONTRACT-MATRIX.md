# APPART.TEST LOCAL PRODUCT DATA ORCHESTRATOR 01 — Step-to-Contract Matrix

| Étape | Contrat observé | Résultat de qualification |
|---|---|---|
| Property | `PropertyRegistry` / authoring certifié | disponible isolément |
| Listing draft | `PropertyListingAuthoringOperations` / `CreateListingDraftV1` | disponible |
| Authoring | `PropertyListingAuthoringOperations` | disponible |
| Submit | `ListingPublicationOrchestrator` | disponible après initialisation du workflow |
| BeginReview | `ListingPublicationOrchestrator` | disponible dans le workflow |
| ApproveAndPublish | `ListingPublicationOrchestrator` | disponible dans le workflow |
| Aggregate publié | `ListingRegistry` | non synchronisé par l'orchestrateur de workflow |
| Search | writer certifié | disponible isolément |
| Content/SEO | writer certifié | disponible isolément |
| Geography | writer certifié | disponible isolément |
| Media | writer certifié | disponible isolément |
| Génération candidate | `PublicProjectionGenerationManager::createCandidate` | disponible |
| Rebuild initial | `PublicProjectionRebuilder` | bloqué : `ActiveGenerationMissing` |
| Manifest | `PublicProjectionGenerationManifest` | interdit les manifestes vides |
| Activation | `PublicProjectionGenerationManager::activate` | bloquée sans candidat reconstruit et validé |
| Reader public | `PublicSearchResultsReaderV1` | opérationnel, retourne `empty` |
| Cleanup | transitions et propagation existantes | non démontrable sans création réussie |
