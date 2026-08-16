# Normal Path

Chemin normatif qualifié:

`autorité bootstrap choisit generationId → createCandidate → inspectForGeneration/rebuild → manifeste → validate/activate → ActiveGenerationReader Found → source assembly runtime → Projection`.

La génération doit être Active avant un appel runtime normal. Aucun `ProjectPublishedListingV1` ne doit inventer ou activer une génération.
