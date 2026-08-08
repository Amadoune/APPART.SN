# Notifications HTTP Risk Register

| Risque | Maîtrise |
|---|---|
| Contournement des Readers | Injection exclusive des interfaces V1 |
| Fuite technique | Réponse limitée au statut |
| Paramètre clandestin | Liste blanche et rejet des champs inconnus |
| Cache de décision sensible | `no-store` |
| Mapping incomplet | Match exhaustif et matrice Unit |
| Ouverture Event/Delivery/Outbox | Aucun composant ni binding |
