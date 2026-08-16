# Alignment Scope

Le futur gate d'implémentation pourra modifier uniquement :

- `build/runtime.lock.json` ;
- `.github/workflows/phase-5.9-reproducible-build.yml` ;
- `tools/release/build-release.sh` ;
- les tests/guards directement nécessaires ;
- la documentation Release correspondante.

Tests obligatoires : successor accepté ; RC2 prédécesseur rejeté comme identité active ; R5 rejeté ; mauvais tag rejeté ; mauvaise relation commit/base rejetée ; syntaxe workflow ; preflight Packaging.

Aucun produit, contrat métier, migration, lockfile ou garde de sécurité ne peut être affaibli.
