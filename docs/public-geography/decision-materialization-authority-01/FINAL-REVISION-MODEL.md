# Final revision model

Le vecteur ordonné est autoritatif. Il contient toutes les paires `(placeId, aggregateVersion)` root→leaf et participe au payload/checksum.

Le watermark scalaire dérivé est la somme vérifiée des versions. Il sert uniquement au protocole monotone du writer et à Candidate readiness. Il ne remplace pas le vecteur et ne constitue pas à lui seul la preuve de cohérence.

RC2 : vecteur `[1,1,1]`, watermark `3`.
