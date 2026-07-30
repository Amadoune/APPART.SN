# Lead Lifecycle Routing Transaction and Concurrency

Le repository rejoint toute transaction PostgreSQL existante. Sinon, il ouvre, valide ou annule sa transaction locale.

Avant l'insert, un verrou advisory transactionnel déterministe est acquis à partir de `messageId`. L'insert utilise `ON CONFLICT (message_id) DO NOTHING`, puis relit et compare tous les champs contractuels.

Deux écritures identiques concurrentes convergent vers `Stored + AlreadyStored` et une ligne unique. Une divergence converge vers `Rejected`. Aucun résultat `Routed` n'est retourné avant validation durable de l'insert ou preuve d'un rejeu strictement identique.

Le rollback d'une transaction externe supprime intégralement la ligne. Aucun état partiel n'est observable.
