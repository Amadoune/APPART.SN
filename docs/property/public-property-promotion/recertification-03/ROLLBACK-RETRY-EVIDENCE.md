# Preuve rollback et retry

Premier passage : Promotion `Applied`, puis défaillance fermée Listing. Le Listing reste Draft, le Workflow reste à sa transition initiale et tous les handoffs locaux sont absents. La Property et son ledger restent chacun uniques.

Second passage : Promotion `AlreadyApplied`, puis Submit réussit. Le Listing atteint `Submitted` et chaque public fact, message outbox, delivery et item PublicationReview apparaît exactement une fois.

Le retry est déterministe et ne duplique aucune occurrence.
