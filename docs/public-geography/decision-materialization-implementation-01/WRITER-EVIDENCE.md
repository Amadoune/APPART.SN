# Writer evidence

Le writer existant accepte désormais V1 ou V2, utilise terminalPlaceId comme clé V2 et conserve son verrou transactionnel/monotone. PostgreSQL démontre Applied, AlreadyApplied, Found et lookup V2. RejectedObsolete/Divergent restent le protocole existant.
