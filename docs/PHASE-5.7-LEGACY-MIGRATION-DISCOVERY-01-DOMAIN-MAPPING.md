# Legacy Migration & Reconciliation — Domain Mapping

## Principe d'autorité

La donnée Legacy est une preuve candidate. Le nouvel owner est l'autorité d'acceptation et devient l'unique source de vérité après cutover.

| Domaine Legacy | Nouvel owner cible | Correspondance autorisée | Transformations interdites | Dépendances de validation |
|---|---|---|---|---|
| Comptes, identités, consentements | `IdentityAccess` | normalisation formelle, statut qualifié, conservation des anciens IDs comme correspondances | réactiver, fusionner ou professionnaliser sans preuve ; reprendre secrets/sessions | Sécurité, protection des données, propriétaires des ressources liées |
| Organisations et représentants | `Professionals` | identité validée, établissement, mandat et profil public approuvé | fusion sur le nom seul ; déplacement de portefeuille sans preuve | IdentityAccess, Listing/Property, Finance si obligation |
| Villes, quartiers, aliases | `Geography` | hiérarchie et aliases approuvés | inventer ou fusionner un lieu sans validation locale | Catalogue, Search, ContentSeo |
| Biens immobiliers | `RealEstateCatalog` | référence, caractéristiques et adresse qualifiées | inventer adresse, surface, type ou rattachement | Geography, propriétaire/mandat |
| Annonces et états | `ListingLifecycle` et owners Authoring certifiés | contenu qualifié et état reclassé par règle approuvée | rendre `Published` un état incertain ; changer de propriétaire sans preuve | IdentityAccess/Professionals, RealEstateCatalog, Media, Moderation |
| Médias et galeries | `Media` | empreinte, ordre, usage et rattachement démontrés | attribution par ressemblance seule ; reprise sans droit | Property/Listing, Moderation, ContentSeo |
| Favoris | owner `Favorites` certifié | paire account/listing acceptée | créer une extrémité manquante ou déduire une préférence | IdentityAccess, Listing public eligibility |
| Contacts et leads | `ContactsLeads` | finalité, consentement, destinataire et historique minimal | conserver indéfiniment ou transmettre sans base légitime | IdentityAccess, Listing, Professionals |
| Réservations | `ReservationLifecycle` | intention et état temporel qualifiés | inventer disponibilité, propriétaire ou confirmation | Listing/Property, IdentityAccess |
| Signalements et décisions | `ModerationReports` | dossier, preuve, décision et recours rattachés | reconstruire une décision non prouvée | Listing, Media, IdentityAccess, AdministrationAudit |
| URL et contenu éditorial | `ContentSeo` | contenu approuvé, redirects/canonicals et aliases qualifiés | utiliser SEO pour activer une ressource métier | Listing eligibility, Geography, Search |
| Index/recherche Legacy | `SearchDiscovery` | comparaison et preuve de couverture uniquement | importer une projection comme autorité métier | projections certifiées des owners |
| Préférences et modèles | `Notifications` | préférence explicite, modèle/canal approuvé | déduire un consentement ou importer un secret fournisseur | IdentityAccess, protection des données |
| Administration et audit | `AdministrationConsole` / `AdministrationAudit` selon la nature | statut console ou preuve d'action explicitement qualifié | migrer des privilèges Legacy comme autorité | IdentityAccess, owner de l'action |
| Paiements et bénéfices | `MonetizationPayments`, sous décision conditionnelle dédiée | preuve financière exacte et droit vérifiable | approximation, fusion entre personnes, activation commerciale implicite | Finance, juridique, IdentityAccess/Professionals |

Les écritures cross-domain, les jointures directes aux tables privées et la reconstruction d'une décision depuis une projection sont interdites.
