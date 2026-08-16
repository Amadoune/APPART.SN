# Operator Command

Surface requise: `appart:projection:generation:bootstrap --generation=<uuid> --listing=<uuid> [--listing=...] --operator=<audit-id>`.

Garde-fous: environnement explicite, confirmation non interactive de la base ciblée, zéro Active, scope non vide, identité fournie, aucun secret, aucun SQL direct. Sortie structurée: generationId, scope checksum, étapes/résultats fermés, Reader final. La commande ne génère pas l'UUID et ne traite pas un rebuild d'Active existante.
