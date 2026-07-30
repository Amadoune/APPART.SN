# Reservation Lifecycle Workflow Analysis

## Décision de périmètre

Aucun Domain Reservation n'existe actuellement. Le Sprint 4.3A crée donc uniquement un slice Application autonome `ReservationLifecycle`, sans Aggregate, identité, repository ni infrastructure.

Le workflow distingue la préparation (`Draft`), la demande soumise (`Requested`), l'engagement accepté (`Confirmed`), l'occupation effective (`InProgress`) et quatre issues irréversibles (`Completed`, `Cancelled`, `Expired`, `Rejected`). Cette séparation évite de confondre refus du propriétaire, expiration temporelle, annulation volontaire et exécution complète.

## Invariants

- Le workflow reçoit seulement un état et une action.
- Les onze transitions sont déclarées explicitement.
- Les quatre états finaux sont terminaux.
- `Unknown` est toujours prioritaire afin de rendre une commande inconnue observable, même sur un état terminal.
- Une action dont la cible nominale est déjà l'état courant produit `IncompatibleState`.
- Toute autre combinaison non déclarée produit `TransitionForbidden`.

Aucune donnée Listing Publication ou Property Lifecycle n'est consultée ou copiée.
