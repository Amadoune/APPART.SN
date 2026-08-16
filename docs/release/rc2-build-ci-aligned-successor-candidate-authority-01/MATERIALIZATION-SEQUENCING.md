# Materialization Sequencing

Deux gates séparés sont requis :

1. `RC2 BUILD/CI SOURCE IDENTITY ALIGNMENT CORRECTION 01` : modifier les surfaces autorisées, adapter les guards, valider, sans commit ni tag.
2. `RC2 SUCCESSOR IMMUTABLE SOURCE MATERIALIZATION 01` : vérifier le périmètre qualifié, créer l'unique commit descendant direct, créer le tag annoté exact, puis produire les preuves externes.

Cette séparation correspond au précédent normatif R4→R5 : alignement logique avant matérialisation, même si le résultat versionné final tient dans un seul commit successor.
