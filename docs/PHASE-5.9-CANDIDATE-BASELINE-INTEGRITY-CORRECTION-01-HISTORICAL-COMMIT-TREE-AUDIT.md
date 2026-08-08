# Historical Commit Tree Audit

Objet audité exclusivement : `1337e225c63e6a3e25c5926f7c4fbddb4ba24da7`, tag annoté `phase-5.9-baseline-candidate`.

`git ls-tree -r --name-only` établit :

- sous `database/` : uniquement `database/.gitignore` ;
- `database/migrations` : absent, Git ne matérialisant pas les répertoires vides ;
- migrations/rollbacks 072–091 : 40 fichiers présents sous `src/Modules/**/Migrations/**` ;
- migrations 090–091 et rollbacks : présents aux chemins modulaires certifiés.

Le `.gitignore` historique n'ignore pas `database/migrations`; `database/.gitignore` ignore uniquement `*.sqlite*`.

Le manifest R1 bornait le staging à `CHANGELOG.md`, `ROADMAP.md`, `bootstrap/providers.php`, `app/**`, `docs/**`, `src/**` et `tests/**`. `database/**` n'y figurait pas. La preuve de clone R1 vérifiait un status propre, mais pas l'existence du répertoire attendu par la gate.
