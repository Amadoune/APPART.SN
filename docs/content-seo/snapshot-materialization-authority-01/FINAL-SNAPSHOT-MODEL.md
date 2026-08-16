# Final Snapshot Model

`ContentSeoSourceDecision` final contient uniquement le contrat existant : snapshotId, ListingId, version, `ListingSeoSource`, `SearchSeoSource`, `PropertySeoSource`, canonical history et decisionAt.

ListingSeoSource reçoit état Published, title et description Authoring sans réinterprétation, canonical UUID, révision Listing, publishedAt, expiresAt et traitements existants. SearchSeoSource reçoit visibilité/révision Search. PropertySeoSource reçoit disponibilité, type, ville et révision Property/Geography.

L’indexabilité n’est pas un champ du snapshot : `ListingSeoDecisionPolicy` la décide ensuite depuis ces sources et Public Geography/Media.
