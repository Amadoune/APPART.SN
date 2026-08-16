# Activate Contract Audit

`activate(generationId, PublicProjectionGenerationManifest)` exige une génération existante en Candidate et une validation réussie. Résultats fermés: Applied, AlreadyApplied, GenerationNotFound, InvalidState, ValidationFailed. L'activation verrouille cible et Active, retire l'ancienne Active éventuelle, puis active la cible atomiquement. Aucune expectedVersion distincte n'existe.
