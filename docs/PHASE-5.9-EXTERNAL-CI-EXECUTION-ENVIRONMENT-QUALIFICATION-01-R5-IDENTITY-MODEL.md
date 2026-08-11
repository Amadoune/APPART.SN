# External CI — R5 Identity Model

Chaîne normative attendue :

`phase-5.9-baseline-candidate-r5` → `9801d9ed30ea3a5fa412708cd022d16bc84e472c` → repository distant officiel → GitHub Actions → `GITHUB_SHA` exact → run ID/attempt → artefact et logs.

Contrôles obligatoires :

1. le tag distant est annoté ;
2. `tag^{commit}` vaut exactement le commit R5 ;
3. le contexte du run expose ce commit comme `GITHUB_SHA` ;
4. le workflow exécuté provient de ce même commit ;
5. le run ID et le run attempt sont permanents ;
6. logs, manifeste, archive et checksums sont rattachés à ce run.

État : modèle cohérent, mais chaîne non instanciée faute de repository distant officiel.
