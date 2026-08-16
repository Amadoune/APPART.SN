# Empty Generation Policy

Une génération vide ne peut pas être activée. `PublicProjectionGenerationManifest` rejette un tableau vide; le Validator exige expected=observed et chaque watermark exact. Aucune exception initiale ou RC2 n'est autorisée. Le bootstrap requiert au moins un Listing Published dont l'assemblage certifié produit un record Candidate.
