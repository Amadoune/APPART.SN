# Compatibilité des Property legacy

Une Property créée avant F6 ou hors ledger n’est ni automatiquement acceptée ni automatiquement rejetée.

- état complet exactement égal au mapping canonique F6, AddressId F2 compris : `AlreadyApplied` sans backfill ;
- toute identité ou fait différent : `DivergentCommand` ;
- aucune mutation de l’Aggregate legacy ;
- aucun ledger de succès fabriqué pour expliquer rétroactivement son origine.

Toutes les données nécessaires sont déjà persistées dans l’Aggregate Property. Aucun backfill ou migration n’est requis.
