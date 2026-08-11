# RC1-A Baseline Manifest

## Source ancestrale

- branche observée : `main` ;
- HEAD : `9801d9ed30ea3a5fa412708cd022d16bc84e472c` ;
- tag exact : `phase-5.9-baseline-candidate-r5` ;
- tree R5 : `92fe3b005c6598b4842a9dd4e6790cb2377efcd8`.

R5 reste immuable. Le futur candidat RC1 devra être un descendant direct de ce commit et contenir l'intégralité du delta qualifié ci-dessous.

## Ensemble candidat

La source candidate est définie exactement par :

1. les 6 446 fichiers suivis par R5, dans leur état courant ;
2. les 73 fichiers suivis actuellement modifiés ;
3. les 559 nouveaux fichiers source qualifiés après création des présents livrables ;
4. aucune suppression suivie ;
5. exclusion unique : `.pnpm-store/v11/index.db`, cache local pnpm non-source.

Delta candidat attendu : **632 chemins** (`73 modified + 559 added`).

La sélection exacte est reproductible par l'union de `git ls-files` et `git ls-files --others --exclude-standard`, après exclusion littérale du chemin ci-dessus. Aucun autre glob ou exclusion implicite n'est autorisé.

## Empreintes gelées

| Fichier | SHA-256 |
|---|---|
| `composer.lock` | `f15dde645598d805143d9ec1d3fab666730ac58ada078b0a3448c498bbd02be5` |
| `package-lock.json` | `1a717514aba144013fe85101e951f18cc74de01f311c9f9b5378b767d00ed26a` |

Les deux lockfiles sont suivis et non modifiés par rapport à R5.

## Identité préparée

- commit candidat proposé : `APPART.SN RC1 candidate baseline` ;
- tag annoté proposé : `appart-sn-release-candidate-rc1` ;
- cible attendue du tag : le futur commit candidat exact ;
- commit et tag : **NOT_CREATED** ;
- staging : **NOT_PERFORMED**.

Le hash du commit et le tree Git final ne peuvent être établis qu'au jalon de matérialisation autorisé.
