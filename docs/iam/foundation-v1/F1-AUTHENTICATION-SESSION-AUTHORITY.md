# F1 — Authentication & Session Authority

## Objet

Qualifier les autorités internes nécessaires à l'authentification et aux sessions IAM sans activer `IdentityAccessHttpRuntime`.

## État observé

Les contrats 5.1 définissent les frontières et invariants, mais pas les décisions exécutables requises :

- `AccountRegistry` sait uniquement retrouver un compte par `AccountId` ;
- aucun `LoginIdentityResolverV1` n'existe pour un identifier normalisé ;
- `PasswordHash` valide et encapsule une représentation encodée, sans produire ni vérifier un hash depuis un credential clair ;
- `Account::passwordMatches()` compare deux représentations déjà encodées et ne constitue pas un verifier plaintext ;
- aucun algorithme/version/paramétrage de hashing credential n'est certifié ;
- aucun générateur ou hasher versionné de secret de session n'existe ;
- le store Session persiste un état normalisé mais ne décide aucune politique ;
- aucune durée idle/absolute, cadence de rotation ou limite de sessions V1 n'est arrêtée.

## Décision

La Foundation ne peut pas être matérialisée sans inventer des autorités de sécurité. Conformément à la mission, l'implémentation s'arrête fail-closed avant tout code partiel. Le binding `FailClosedIdentityAccessHttpRuntime` reste inchangé.
