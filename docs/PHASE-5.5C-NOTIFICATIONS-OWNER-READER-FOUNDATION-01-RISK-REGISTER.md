# Notifications Owner Reader Risk Register

| Risque | Maîtrise |
|---|---|
| Nouvelle décision métier | Mapping homonyme exhaustif |
| Source secondaire | Dépendance unique à `NotificationsOwnerSource` |
| Accès Persistence concret | Port Application uniquement |
| Fallback implicite | Match fermé ; états incompatibles rejetés |
| Modification des contrats | Types V1 consommés sans modification |
| Régression migration | SHA-256 de 079 vérifié en Architecture |
