# Identity model

L'identité persistée est `MediaCollectionId`, conformément au reader, writer et store. Listing fournit le `mediaCollectionId` publié; Property établit l'ownership de collection; les items conservent leur `MediaId`.

Aucun UUID de décision aléatoire n'est requis. La cohérence Listing → Property → MediaCollection doit être vérifiée avant assemblage.
