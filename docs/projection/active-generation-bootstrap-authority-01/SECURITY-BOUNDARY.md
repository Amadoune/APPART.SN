# Security Boundary

La commande refuse Active existante, base non explicitement ciblée, identité absente/invalide, scope vide ou changement de scope au replay. Elle ne supprime rien, ne réactive aucune Retired, ne génère aucune identité silencieuse et n'expose aucun secret. GenerationId, operator audit-id, environnement et résultats sont consignés. Accès CLI limité au deployment operator.
