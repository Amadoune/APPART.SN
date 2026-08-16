# Media materialization path

Chemin attendu : faits Media owner et disponibilité publique → assembleur de décision Media → `PublicMediaDecisionWriter` → `public_media.decisions` → reader Projection.

Audit : reader, mapper, writer et store existent. Aucun consumer Published, orchestrateur générique, commande productive ni catch-up n'a été trouvé. La commande locale P02 n'est pas une autorité productive.
