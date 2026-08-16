# Indexability Authority

La policy existante `ListingSeoDecisionPolicy` est propriétaire de l’indexabilité finale. Elle exige simultanément : Listing Published, Search Public, Property Available, Geography et Media publics, contenu valide et dates publication/expiration cohérentes.

Elle produit `Indexable`/`NotIndexable`, robots, traitement de page et JSON-LD. Published seul ne suffit pas. Le snapshot ne doit donc pas écrire arbitrairement `indexable=true`.
