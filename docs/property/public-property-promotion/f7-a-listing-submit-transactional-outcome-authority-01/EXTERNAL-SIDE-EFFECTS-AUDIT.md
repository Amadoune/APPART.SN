# Audit des side effects

| Side effect pendant Submit | Nature | Transactionnel avec Listing | Irréversible externe |
|---|---|---:|---:|
| Aggregate et révisions Listing | PostgreSQL local | oui | non |
| public-facts candidate | PostgreSQL local/savepoint | oui | non |
| transition Workflow | PostgreSQL local | oui | non |
| messages et deliveries Outbox | PostgreSQL local/savepoint | oui | non |
| ingestion PublicationReview | PostgreSQL local/savepoint | oui | non |
| génération d’événements/messages | calcul mémoire | sans mutation externe | non |

Aucun HTTP, broker, email, notification, fichier ou queue externe n’est appelé dans cette frontière. L’outbox est une écriture locale et sa livraison se produit ultérieurement. Aucun side effect irréversible ne précède le verdict transactionnel.
