# Initial versus refresh

- Initial : ListingPublished → même materializer.
- Refresh : mutation Media ou delivery publiquement pertinente → même materializer.
- Catch-up : ListingId Published → même materializer.

Les trois chemins doivent utiliser les mêmes readers owner, politique, révision et writer. Aucun chemin RC2 spécifique.
