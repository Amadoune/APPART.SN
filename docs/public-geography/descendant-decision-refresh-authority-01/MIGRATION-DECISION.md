# Migration decision

**NO MIGRATION.** JSONB V2 permet le lookup fonctionnel par vector et le stockage Available/Unavailable. La PK place_id fournit la pagination.

Un index GIN pourrait optimiser une volumétrie démontrée, mais n'est pas nécessaire à la correction fonctionnelle et n'est pas autorisé ici.
