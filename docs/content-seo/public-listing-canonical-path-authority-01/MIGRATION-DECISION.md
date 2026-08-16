# Migration Decision

**Aucune migration.**

Le path est calculable depuis ListingId. La table de snapshot accepte la valeur dans son payload ; Public Projection possède déjà sa clé canonical et ses protections de collision ; Historical Canonical/Redirect possède ses stores propres.
