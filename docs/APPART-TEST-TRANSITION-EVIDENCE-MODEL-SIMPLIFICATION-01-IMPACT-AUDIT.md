# Impact Audit

Jalon : `APPART.TEST TRANSITION EVIDENCE MODEL SIMPLIFICATION 01`.

| Surface | Obligatoire avant | Lecture fonctionnelle | Persistance | Impact de l'absence |
|---|---:|---:|---:|---|
| `TransitionEvidence` | oui | non | indirecte | signature à rendre optionnelle |
| `ListingRevision` | oui | non | oui | type nullable requis |
| Aggregate `Listing` | oui | non | oui | copie mécanique inchangée |
| `ListingTransitionPolicy` | implicite | oui, pour valider la présence | non | exception limitée aux trois triggers certifiés |
| Events Listing | oui | non | métadonnée | contrat nullable, valeur existante conservée |
| `ListingMapper` / snapshot | oui | non | oui | mapping bidirectionnel de `null` |
| PostgreSQL | `NOT NULL` | non | oui | migration additive 092 nécessaire |
| Projections / Outbox / Transport | non consommé | non | aucune dépendance trouvée | aucun changement |

`SubmitListing`, `SendToReview` et `PublishListing` ne prennent aucune décision à partir du motif. Le motif est une trace descriptive copiée dans la révision et l'événement. Aucun consommateur aval fonctionnel n'a été identifié.
