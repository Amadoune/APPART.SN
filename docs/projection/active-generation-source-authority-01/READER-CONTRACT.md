# Reader Contract

- Port: `App\Application\ActiveGenerationReader\Contract\ActiveGenerationReader`.
- Méthode: `read(): ActiveGenerationReadResult`, sans entrée.
- Résultats fermés: `Found`, `Missing`, `Corrupted`.
- `Found` transporte `PublicProjectionGeneration(id, state=Active)`.
- Identité: UUID canonique `PublicProjectionGenerationId`.
- Aucune version/révision distincte n'est exposée.
- Zéro Active = Missing; plusieurs Active ou mapping invalide = Corrupted.
