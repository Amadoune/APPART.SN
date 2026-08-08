# Backup / Restore / DR Evidence

| Exigence | Owner | Preuve attendue | Preuve observée | Emplacement | Statut | Justification | Action requise |
|---|---|---|---|---|---|---|---|
| Backup DB | Data Owner | politique, chiffrement, rétention, succès | aucune | aucun | MISSING | aucune preuve récupérable | définir/attester séparément |
| Backup fichiers | Operations | périmètre objet/local et rétention | aucune | aucun | MISSING | médias et artefacts non couverts | inventaire et politique |
| Restore | Data Owner | restauration chronométrée | aucune | aucun | BLOCKED | RPO/RTO non vérifiables | exercice obligatoire |
| Intégrité post-restore | Domain Owners | contrôles référentiels | aucune | aucun | BLOCKED | cohérence inconnue | protocole de validation |
| DR | Operations | plan, site, responsabilités | aucune | aucun | BLOCKED | continuité non démontrée | plan et exercice séparés |
| RPO/RTO | Release Authority | objectifs approuvés et mesurés | aucune | aucun | MISSING | critères GO impossibles | décision d'autorité |

