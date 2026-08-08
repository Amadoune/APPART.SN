# Runtime Pinning Evidence

Source certifiée : `1337e225c63e6a3e25c5926f7c4fbddb4ba24da7` / `phase-5.9-baseline-candidate`.

| Composant | Version requise | Identité immuable | Preuve |
|---|---|---|---|
| PHP | 8.5.8 NTS | valeur exacte du workflow | `build/runtime.lock.json` |
| Composer | 2.9.4 | PHAR SHA-256 `d3eb5c4cb2e708267dac5f9a76d2a57b07836c7ea68783a1a02bd6d94753ea80` | source officielle Composer |
| Node.js | 24.17.0 | archive Linux x64 SHA-256 `ab343a1b747c7cbf3630dfd7dbf818c5423fab2eb4f5ad1afc896f6bd121a917` | SHASUMS officiel Node.js |
| npm | 11.13.0 | distribution associée au runtime Node verrouillé | runtime lock |
| PostgreSQL | 18.4 | image OCI `sha256:a02db8cac496f15b094798a38254f14d6e00741f709360e5e00bb6668ea31636` | service CI |
| OS CI | Ubuntu 24.04 | version de runner + identité d'image consignée à l'exécution | workflow |

Les actions tierces sont référencées par SHA Git complet, jamais par tag flottant.
