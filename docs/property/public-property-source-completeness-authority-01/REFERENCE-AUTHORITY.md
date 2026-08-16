# Property Reference Authority

| Option | Constat | Décision |
|---|---|---|
| Saisie propriétaire | Source explicite, rejouable et versionnable ; format Domain et unicité Registry restent autoritatifs | **Retenue comme modèle cible** |
| Génération RealEstateCatalog déterministe | Aucun contrat ou algorithme existant observé ; choisir un format serait une nouvelle règle | Rejetée |
| Réservation existante | Le Registry ajoute atomiquement et détecte les conflits, mais aucun service préalable de réservation/génération n'existe | Non disponible |

Champ cible : `propertyReference`, chaîne owner-authored normalisée uniquement par `PropertyReference::fromString`. Le client propose la valeur ; RealEstateCatalog reste l'autorité d'acceptation et d'unicité. Un conflit est fermé et ne déclenche aucune génération de secours.

Les constantes P02 et les références aléatoires sont interdites.
