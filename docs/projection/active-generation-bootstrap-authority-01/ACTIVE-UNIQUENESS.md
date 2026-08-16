# Active Uniqueness

Une seule Active globale par base/environnement, garantie par index partiel unique. Zéro Active autorise le bootstrap. Une Active fait retourner à la commande un refus fermé `ActiveAlreadyExists`, sauf replay de la même génération déjà active, classé `AlreadyApplied`. Plusieurs Active observées sont `Corrupted`; aucune mutation réparatrice automatique.
