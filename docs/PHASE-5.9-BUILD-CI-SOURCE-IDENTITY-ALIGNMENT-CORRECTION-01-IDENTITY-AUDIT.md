# Source Identity Audit

## Modèle historique

Dans Evidence 02, R1 était vérifiée comme tag immuable résolvant vers un commit fixe, puis comme ancêtre du commit effectivement construit. Elle représentait donc une **source de base**, pas l'identité exacte du build. Le verrou Runtime réutilisait les noms `sourceCommit` et `sourceTag`, tandis que le workflow et le packaging acceptaient tout descendant de R1. R2 a hérité de ces trois références inchangées, causant le FAIL Evidence 03.

## Modèle corrigé

| Contrôle | Identité vérifiée | Moment | Politique |
|---|---|---|---|
| `build/runtime.lock.json` | source de base R2 + nom du tag candidat R3 | lecture préalable | déclaration normative sans SHA propre |
| workflow CI | tag annoté R3, commit checkouté, ascendance R2 | avant restauration | le tag doit résoudre exactement vers `GITHUB_SHA`; R2 doit être ancêtre |
| packaging | tag annoté R3, `HEAD`, ascendance R2 | avant packaging | le tag doit résoudre exactement vers `HEAD`; R2 doit être ancêtre |

R3 n'inscrit jamais son propre SHA dans son contenu. Son identité exacte est établie après création du commit par un tag annoté immuable. Le commit R2 reste une référence antérieure connue et peut donc être inclus sans auto-référence.

La convention unique est : `annotated-candidate-tag-resolves-head-and-source-base-is-ancestor`.
