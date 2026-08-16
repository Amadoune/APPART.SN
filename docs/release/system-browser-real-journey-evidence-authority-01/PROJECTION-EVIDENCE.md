# Preuve Projection

Le terminal d'Iteration 11 reste strictement :

`Published → Source Assembly → Projection materialized → replay idempotent`.

Preuves exigées :

1. photographie autoritative avant Projection : Property présente, Aggregate et Workflow Published, Queue Completed, promotion ledger présent ;
2. exécution du mécanisme productif `CertifiedPublicListingProjectionSource` ou de son chemin productif exact ;
3. statut d'assembly réussi et disparition explicite de `PropertyMissing` ;
4. cohérence PropertyId, AddressId, GeographicPlaceId et données Listing ;
5. read model réellement persisté dans le Projection Store ;
6. activation ledger unique ;
7. replay avec même command identity et même source revision/checksum ;
8. `AlreadyApplied` ou résultat idempotent certifié ;
9. nombre de projections et d'activations inchangé après replay ;
10. contenu identique.

Au premier statut fermé différent du succès, la campagne s'arrête. Search n'est jamais interrogé.
