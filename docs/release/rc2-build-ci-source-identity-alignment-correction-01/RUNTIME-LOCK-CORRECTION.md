# Runtime Lock Correction

`build/runtime.lock.json` conserve son schéma, ses versions et ses checksums.

Seuls les champs d'identité ont changé :

- `sourceBaseCommit` → commit RC2 prédécesseur ;
- `candidateTag` → tag successor exact.

La politique annotée `tag → HEAD` avec ascendance de la base est préservée.
