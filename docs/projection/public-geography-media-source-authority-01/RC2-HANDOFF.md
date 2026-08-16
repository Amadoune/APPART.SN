# RC2 handoff

Avant de rouvrir `ACTIVE GENERATION BOOTSTRAP IMPLEMENTATION 01` :

- le reader Geography doit retourner `Found` pour `c3120000-0000-4000-8000-000000000003`, version positive;
- le reader Media doit retourner `Found` pour `71fae610-6d12-5ad2-9c13-572c8eb1658c`, couverture publique et version positive;
- les deux écritures doivent provenir des chemins productifs et supporter le replay;
- l'assembly doit rester `Found` et la readiness devenir `ready`.

Le GenerationId réservé `da5484c1-a351-4683-9881-79f34c51197f` reste inutilisé jusque-là.
