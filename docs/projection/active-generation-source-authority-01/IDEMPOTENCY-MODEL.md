# Idempotency Model

Recréer la même Candidate retourne `AlreadyApplied`. Réactiver une génération déjà Active retourne `AlreadyApplied`. Le rebuild converge via le writer candidat et ses watermarks. Une identité stable est donc obligatoire sur tous les replays; sa source reste à autoriser par le prochain gate.
