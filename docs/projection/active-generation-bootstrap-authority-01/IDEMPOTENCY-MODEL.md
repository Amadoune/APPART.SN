# Idempotency Model

Même generationId + même scope = même bootstrap logique. Create rejoué: AlreadyApplied. Records reconstruits: AlreadyApplied si identiques. Activate rejoué après succès: AlreadyApplied. Le scope canonique ne peut changer pendant le replay; sinon arrêt divergent avant mutation. Aucun nouveau generationId n'est généré automatiquement.
