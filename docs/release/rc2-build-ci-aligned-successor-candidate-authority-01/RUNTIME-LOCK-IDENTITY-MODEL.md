# Runtime Lock Identity Model

Le futur alignement de `build/runtime.lock.json` doit conserver le schéma et les pins Runtime existants.

Seuls les champs d'identité deviennent :

- `sourceBaseCommit` : `ab5d3f57a577160d3aae36cee5778dc7bae59a16` ;
- `candidateTag` : `appart-sn-release-candidate-rc2-r2` ;
- `identityPolicy` : politique fermée équivalente à `annotated-candidate-tag-resolves-head-and-source-base-is-ancestor`.

Aucun SHA de commit successor ni tree successor n'est ajouté.
