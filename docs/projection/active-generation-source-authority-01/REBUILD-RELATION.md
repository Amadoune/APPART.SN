# Rebuild Relation

Le `PublicProjectionRebuilder` énumère les Listings, assemble chaque record pour une Candidate explicite et écrit uniquement dans cette génération. Le checkpoint est opaque et le replay idempotent. Le runtime incrémental cible l'Active; le rebuild prépare une Candidate. Une génération sert donc au bootstrap initial comme aux reconstructions ultérieures.
