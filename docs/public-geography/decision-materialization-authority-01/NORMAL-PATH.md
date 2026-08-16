# Normal path

Chemin envisagé mais non autorisé :

```text
ListingPublished -> resolve Listing/Property/Place
-> read canonical public Place representation
-> build PublicGeographyDecision
-> PublicGeographyDecisionWriter
-> PublicGeographyDecisionReader Found/version positive
-> Candidate readiness Geography PASS
```

Le maillon `canonical public Place representation` est absent.
