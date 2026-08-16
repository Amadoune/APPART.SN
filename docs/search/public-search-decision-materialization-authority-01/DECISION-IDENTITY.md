# Identité de décision

`SearchDecision` attend un `SearchIndexId`, UUID valide. Le writer indexe la décision courante par ListingId et impose l'unicité du decisionId.

Aucune factory déterministe pour la décision finale, ni convention reliant eventId, ListingId et SourceRevisionSet, n'existe dans le pipeline audité. Les commandes locales emploient des UUID constants spécifiques à leurs fixtures.

L'identité logique devrait être stable pour la même intention et les mêmes sources, mais sa formule ne peut pas être décidée tant que le rang, les facettes et la révision Property ne le sont pas.

**Autorité d'identité non fermée.**

## Completion 01

Constat historique fermé. L'identité finale est l'UUIDv5 défini dans `FINAL-DECISION-IDENTITY.md`, stable par ListingId et policyId, sans eventId, commandId, horloge ou random.
