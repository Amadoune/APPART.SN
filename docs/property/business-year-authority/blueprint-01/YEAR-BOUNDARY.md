# Year Boundary

Frontière normative UTC :

- `2026-12-31T23:59:59.999999Z` → BusinessYear 2026 ;
- `2027-01-01T00:00:00.000000Z` → BusinessYear 2027.

Une commande créée avant la frontière et rejouée après conserve son occurredAt 2026 et résout 2026. Une commande réellement nouvelle après la frontière résout 2027.

Un offset local est d'abord converti vers UTC ; la date locale affichée n'est jamais décisive.
