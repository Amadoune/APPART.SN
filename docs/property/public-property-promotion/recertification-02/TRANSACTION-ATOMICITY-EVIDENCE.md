# Preuves d’atomicité

## F6

Property et ledger Promotion sont atomiques ; l’échec ledger rollbacke Property.

## Listing après F7-A

Aggregate, Workflow, public facts, outbox, deliveries et PublicationReview committent ensemble sur succès et rollbackent ensemble sur closed failure. Property et ledger F6 restent durablement séparés.

Ces deux frontières sont qualifiées. Elles ne compensent pas la divergence d’identité Address lors de la comparaison d’une Property existante.
