# Sprint 3.8D — analyse d’architecture

La décision durable est indexée par `placeId` et associe atomiquement la révision 3.7A à la locality et au breadcrumb déjà décidés. Le modèle applicatif produit une représentation JSON canonique du contenu et exige que son SHA-256 soit exactement le checksum de révision.

Une seule ligne PostgreSQL contient version, causalité, checksum de révision et payload. Une contrainte impose l’égalité entre checksum de révision et checksum du payload. L’infrastructure ne choisit aucune locality et ne construit aucun breadcrumb.

Le reader spécialisé distingue `Found`, `Missing` et `Corrupted`. Il implémente également `PublicGeographyRevisionReader`; ce contrat historique retourne la révision uniquement lorsque la décision complète est valide.
