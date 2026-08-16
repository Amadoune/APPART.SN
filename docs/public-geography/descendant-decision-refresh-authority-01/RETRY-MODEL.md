# Retry model

Source event → pages déterministes → résultats terminaux. Un échec dependency d'un terminal retourne RetryableFailure pour le message source; le replay repart de la première page et AlreadyApplied absorbe le travail réussi.

Corrupted/Divergent est PermanentFailure/quarantine. Aucun terminal échoué n'est masqué par les succès précédents.
