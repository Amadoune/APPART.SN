# Create Contract Audit

`PublicProjectionGenerationManager::createCandidate(PublicProjectionGenerationId)` crée uniquement l'état Candidate. Résultats: `Applied`, `AlreadyApplied`, `InvalidState`; une erreur de persistance remonte fail-closed. Même Candidate = AlreadyApplied; Active/Retired avec la même identité = InvalidState. L'opération utilise une transaction owner-locale et ne valide aucun contenu.
