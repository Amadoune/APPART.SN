# IAM Boundary

Le catalogue Geography est une donnée de référence commune. Le reader applicatif n'exige aucune capacité IAM spécifique et son entrée ne contient ni AccountId ni owner scope.

Une surface Authoring peut rester protégée par sa session IAM pour son propre parcours, mais l'identité du compte n'influence ni les Places visibles ni leur selectability. Le client ne peut fournir aucun ownerAccountId au reader.

Cette décision ne modifie IAM, ses rôles, ses sessions ou ses policies.
