# Rapport de compatibilité

La décision :

- conserve le catalogue d’outcomes Workflow existant ;
- conserve le mapping HTTP/Application existant ;
- réutilise le signal interne de rollback existant ;
- ne modifie aucun contrat transactionnel ;
- ne modifie aucun store ou schéma ;
- préserve la séparation Promotion/Listing ;
- ne touche pas F6 ni les autorités antérieures ;
- ne crée aucune dépendance Projection/Search.

La correction future est localisée, additive dans son contrôle de flux et réversible. Aucune migration n’est nécessaire.
