# Baseline Correction Boundary

Modifications autorisées et réalisées :

- `database/migrations/.gitkeep` ;
- trois admissions nominatives dans `InfrastructureBaselineArchitectureTest` : repository exact, préfixe Infrastructure Outbox exact, slice Migrations Outbox exacte ;
- onze preuves et cinq registres normatifs.

Non modifiés : code métier, contrats, Runtime, repository ExperienceAcceptance, comportement Outbox, SQL, migrations/rollbacks, lockfiles, workflow CI et tags R1/Build-CI historiques.

Aucune gate n'est supprimée ou affaiblie ; les admissions correspondent exclusivement à la Foundation Outbox 5.8C historiquement certifiée.
