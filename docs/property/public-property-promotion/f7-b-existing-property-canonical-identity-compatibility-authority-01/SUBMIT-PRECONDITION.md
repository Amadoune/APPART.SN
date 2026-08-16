# Précondition Submit

Submit poursuit uniquement après :

- `Applied` ;
- `AlreadyApplied` provenant d’un replay ledger identique ;
- `AlreadyApplied` provenant d’une Property sans ledger démontrée canoniquement compatible.

Une comparaison descriptive partielle n’est pas suffisante. `DivergentCommand`, notamment pour AddressId différent, est réduit par la composition existante et interdit toute transition Listing.
