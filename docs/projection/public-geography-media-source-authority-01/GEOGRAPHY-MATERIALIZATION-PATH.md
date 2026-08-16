# Geography materialization path

Chemin attendu : faits Geography owner → assembleur de décision Geography → `PublicGeographyDecisionWriter` → `public_geography.decisions` → reader Projection.

Audit : reader, mapper, writer et store existent. Aucun consumer Published, orchestrateur générique, commande productive ni catch-up n'a été trouvé. `CreateLocalFirstListing` fabrique une décision P02 spécifique et n'est pas un pipeline productif réutilisable.
