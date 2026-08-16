# Determinism

Même état owner → même chaîne → même payload, vecteur, somme, checksum et causalité. La lecture suit les parentIds, puis inverse la chaîne; aucun ordre SQL implicite.

Clock, random, UUID de décision, locale UI et état Projection sont interdits.
