# Writer Evidence

La composition réutilise `PostgreSqlContentSeoSourceSnapshotReader`, `PostgreSqlContentSeoSourceSnapshotWriter`, leur mapper et la table existante. La transaction, le verrou advisory, le contrôle de version et les statuts terminaux restent owner-locaux ContentSeo.
