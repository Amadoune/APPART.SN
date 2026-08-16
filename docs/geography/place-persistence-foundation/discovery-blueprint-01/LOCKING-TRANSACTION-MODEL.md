# Locking and Transaction Model

## Add

Transaction locale Geography : insertion root puis aliases. Les contraintes DB traduisent les collisions id et `(country_code, code)` vers les exceptions Domain existantes. Tout échec rollback l'ensemble.

## Save

Transaction locale : `UPDATE ... WHERE id=:id AND aggregate_version=:expected`; rowCount différent de 1 produit `ConcurrentPlaceModification`. Puis remplacement atomique de la collection aliases. Root et aliases commit ensemble.

La version candidate doit être supérieure à expectedVersion. Les FK parent/merge sont vérifiées au commit. Aucun verrou distribué ou transaction avec lifecycle/Projection n'est introduit.

Les événements Domain restent libérés/acheminés par l'orchestration propriétaire si une composition événementielle existante l'exige ; le Repository snapshot ne crée aucune nouvelle règle d'événement.
