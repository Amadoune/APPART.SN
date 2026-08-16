# Ordering Guard Evidence

Le guard analyse les étapes nommées du workflow et impose :

`Restore locked dependencies`
avant `Frontend production build`
avant suites PHP, PostgreSQL, analyse statique et packaging.

Il impose aussi une occurrence unique de `run: npm run build`. Résultat : PASS.

Cette preuve évite une simple recherche non ordonnée de chaînes.
