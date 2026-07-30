# Lead Listing–Advertiser Ownership Model

## Propriétaire

La relation normative appartient exclusivement à **ListingLifecycle**. Elle exprime le destinataire Advertiser autorisé à recevoir les Leads d'un Listing ; elle ne représente ni l'auteur d'une transition, ni un acteur de modération.

## Cardinalité et identité

- un Listing possède zéro ou un destinataire normatif à une révision donnée ;
- un Advertiser peut être destinataire de plusieurs Listings ;
- l'identité est le couple `ListingId + EligibilityRevision` ;
- un remplacement produit une nouvelle version, sans modifier l'historique ;
- deux destinataires simultanés pour le même Listing et la même révision sont interdits.

## Cycle de vie

La relation est déclarée, remplacée ou retirée par une décision propriétaire ListingLifecycle. Sa validité commence à l'instant effectif de la révision. Une relation retirée ne peut rendre un ancien lot à nouveau courant.

`listing_revisions.actor_id` n'est jamais une source de cette relation. La future matérialisation reçoit le fait relationnel explicitement, sans reconstruction.
