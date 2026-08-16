# Step Restoration

Le step restauré est le premier point produit non satisfait :

1. transactionKind absente : Projet ;
2. type, référence, surface, pièces ou salles de bain absents : Type ;
3. GeographicPlaceId, AddressIntentId ou addressLine absents : Adresse ;
4. titre, description, transaction, prix, devise ou contact absents : Détails ;
5. collection sans média `ready` : Photos ;
6. toutes les données complètes, Aggregate/Workflow `draft` : Aperçu ;
7. Envoi est atteint uniquement après confirmation de l’Aperçu par l’utilisateur.

Une incohérence de versions ou d’états ne choisit aucun step : elle retourne `StateConflict`. Le navigateur ne persiste pas le numéro de step comme autorité.

Pour le draft RC2 courant, les faits annoncés conduisent à l’étape Aperçu, avec passage utilisateur vers Envoi.
