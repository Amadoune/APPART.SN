# Release Identity Audit

RC, commit, tree, tag et build identifient le logiciel, pas l'epoch des données projetées. Une même release peut servir plusieurs environnements et plusieurs rebuilds; inversement un rebuild peut survenir sans nouvelle release. Aucun précédent ne certifie ce couplage. Ces identités peuvent figurer dans la preuve d'exploitation, jamais dériver generationId.
