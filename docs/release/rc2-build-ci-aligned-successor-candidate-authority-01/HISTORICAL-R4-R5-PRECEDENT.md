# Historical R4 to R5 Precedent

R4 est le commit `058719f8aa154466056299b8c26bd7d51f944127`, tagué par le tag annoté `phase-5.9-baseline-candidate-r4`.

La correction d'alignement a préparé, avant matérialisation :

- `build/runtime.lock.json` ;
- `.github/workflows/phase-5.9-reproducible-build.yml` ;
- `tools/release/build-release.sh` ;
- le test Architecture d'identité associé ;
- les preuves documentaires correspondantes.

Ces surfaces ont inscrit le commit R4 connu comme `sourceBaseCommit` et le futur nom exact `phase-5.9-baseline-candidate-r5`. Elles n'ont inscrit ni SHA ni tree R5.

Un unique commit descendant direct de R4, `9801d9ed30ea3a5fa412708cd022d16bc84e472c`, a matérialisé R5 avec le message `chore(phase-5.9): materialize baseline candidate r5`. Le tag annoté R5 a ensuite été créé et résout vers ce commit. Le modèle évite ainsi toute auto-référence cryptographique.
