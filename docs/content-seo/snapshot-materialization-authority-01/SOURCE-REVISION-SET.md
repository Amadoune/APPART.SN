# Source Revision Set

Le mapper existant impose exactement trois `SeoSourceRevision` : Listing, Search, Property.

- Listing : authoringVersion, sourceIntentId, publishedAt ; le handoff Published prouve l’éligibilité et lie publishedRevisionId dans la cohérence.
- Search : SearchDecision version, decisionId, révision effective de la décision.
- Property : promotion authoringVersion, promotion commandId ; le fait composé inclut Property active, type et Geography city/version.

Un `coherenceId` UUIDv5 commun doit être dérivé du tuple canonique des identités/révisions owner, incluant publishedRevisionId et Geography revision. Media est exclu du snapshot : Public Media est lu séparément par Projection.
