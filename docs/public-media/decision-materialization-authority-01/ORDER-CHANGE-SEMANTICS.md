# Order change semantics

Un reorder modifie `MediaCollection::version()`, l'ordre du payload canonique et son checksum. Il doit donc produire une nouvelle révision Public Media, sous réserve que le modèle de révision final incorpore aussi l'autorité URL.

Un replay du même ordre et des mêmes sources ne produit aucune nouvelle ligne/version.
