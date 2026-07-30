# Public Geography Durable Source

Le writer reçoit une décision publique complète déjà produite. Les résultats sont `Applied`, `AlreadyApplied`, `RejectedObsolete` et `Divergent`. La version est monotone et la causalité participe à l’identité du fait.

Le reader retourne locality, breadcrumb et révision exacts. Une corruption JSON, une incohérence de checksum ou une valeur invalide produit `Corrupted`, sans fallback.
