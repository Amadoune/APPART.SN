# Reader Binding Audit

Adapter productif : `PostgreSqlContentSeoSourceSnapshotReader`.

Binding : `PublicProjectionRuntimeServiceProvider` lie `ContentSeoSourceSnapshotReader` à cet adapter. `bootstrap/providers.php` charge le provider.

Le reader est opérationnel. Le résultat RC2 `Missing` provient d’une absence de ligne, pas d’un défaut de résolution du container.

Le mapper vérifie les Value Objects, les identités, les révisions et le SHA-256 du payload ; toute erreur devient `Corrupted`.
