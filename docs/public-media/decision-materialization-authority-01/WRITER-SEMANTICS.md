# Writer semantics

`PostgreSqlPublicMediaWriter` est transactionnel localement, verrouille par collection, relit sous `FOR UPDATE`, puis arbitre Applied/AlreadyApplied/RejectedObsolete/Divergent. Mapper et contraintes vérifient les checksums.

Il n'écrit ni Listing, ni Property, ni Projection, ni Search. Aucun changement writer ou SQL Application n'est requis par le présent audit.
