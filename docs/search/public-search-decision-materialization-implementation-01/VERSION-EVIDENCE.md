# Version Evidence

La première décision reçoit la version `1`. Un `SourceRevisionSet` identique conserve la version. Un ensemble strictement dominant produit `version + 1`. Un ensemble obsolète est rejeté ; un mélange ou une même version portant un fait différent est divergent.

Les tests unitaires couvrent première écriture, replay, source dominante, source obsolète et divergence. Le test PostgreSQL démontre le passage stable de version 1 à 2 lors d’une révision Media dominante.
