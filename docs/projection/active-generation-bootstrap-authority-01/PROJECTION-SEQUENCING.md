# Projection Sequencing

Le bootstrap matérialisera une Candidate par le Rebuilder puis l'activera. Après Reader Found, seule une inspection read-only de `CertifiedPublicListingProjectionSource` est autorisée au handoff RC2. `ProjectPublishedListingV1` n'est pas appelé automatiquement. La prochaine divergence sera qualifiée séparément.
