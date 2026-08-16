# Generation ID Authority

Source normative: UUIDv4 produit une seule fois par le générateur cryptographique du système d'exploitation ou du pipeline de déploiement, puis passé explicitement à la commande. La commande ne génère jamais silencieusement l'identité. UUIDv5, commit, tag, RC et constantes de test sont rejetés: aucune relation sémantique historique ne les lie à une epoch de read model.

La preuve opérateur doit enregistrer environment, generationId, acteur et résultat, sans secret.
