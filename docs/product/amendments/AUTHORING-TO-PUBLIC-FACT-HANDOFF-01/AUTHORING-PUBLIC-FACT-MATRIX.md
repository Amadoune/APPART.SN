# Authoring → Public Fact Matrix

| Champ | Owner actuel | Moment possible de changement | Owner public | Transport actuel | Décision |
|---|---|---|---|---|---|
| `listingId` | Listing Lifecycle | aucun | Listing Lifecycle | présent | Identité, pas un fait à promouvoir |
| `propertyId` | Real Estate / Listing Lifecycle | aucun | corrélation interne de projection | présent | Interne, non exposé par Search |
| `title` | Authoring mutable | publication | Content/SEO | déjà assuré par la décision Content/SEO | Ne pas dupliquer dans le handoff |
| `description` | Authoring mutable | publication | Content/SEO | déjà assuré par la décision Content/SEO | Ne pas dupliquer dans le handoff |
| `transactionKind` | Authoring mutable | publication approuvée | Listing Lifecycle, version publiée | manquant | PROMOTE via handoff |
| `priceMinor` | Authoring mutable | non qualifié | non décidé | manquant | PRIVATE_PENDING_PRODUCT_DECISION |
| `currency` | Authoring mutable | avec prix, non qualifié | non décidé | manquant | PRIVATE_PENDING_PRODUCT_DECISION |
| `chargesMinor` | Authoring mutable | non qualifié | non décidé | manquant | PRIVATE |
| `availabilityDate` | Authoring mutable | non qualifié | non décidé | manquant | PRIVATE |
| `contactPreference` | Authoring | jamais par défaut | Contacts/Lead boundary éventuelle | aucun | PRIVATE |
| `version` | Authoring Persistence | jamais | aucun owner public | interne | TECHNICAL_EVIDENCE |
| `intentId` | Authoring Application | jamais | aucun owner public | interne | IDEMPOTENCY_EVIDENCE |
| `intentChecksum` | Authoring Persistence | jamais | aucun owner public | interne | INTEGRITY_EVIDENCE |

## Conclusion

L'audit porte sur l'intégralité de `ListingDraftState`. Dans l'état des décisions produit, seul `transactionKind` doit rejoindre le nouveau handoff. Les autres champs publics visibles sont déjà gouvernés par Content/SEO ou ne possèdent pas encore d'autorité produit permettant leur exposition.
