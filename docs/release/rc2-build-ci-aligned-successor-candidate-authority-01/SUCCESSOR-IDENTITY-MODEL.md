# Successor Identity Model

L'identité versionnée minimale est :

- base ancestrale connue : commit RC2 `ab5d3f57a577160d3aae36cee5778dc7bae59a16` ;
- nom symbolique fermé de la future candidate : `appart-sn-release-candidate-rc2-r2` ;
- politique : le tag doit être annoté, résoudre exactement vers le commit exécuté et la base RC2 doit en être un ancêtre.

Le SHA du commit et le tree successor sont produits par Git lors de la matérialisation puis consignés dans les preuves post-matérialisation externes. Ils ne sont jamais hardcodés dans leur propre tree.
