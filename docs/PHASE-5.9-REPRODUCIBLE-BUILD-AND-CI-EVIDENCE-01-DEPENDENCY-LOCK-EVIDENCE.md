# Dependency Lock Evidence

| Élément | Preuve | Statut |
|---|---|---|
| composer.json SHA-256 | 47117ce1fb6223b220d169af67862c80a240fba98f66a89afbbbe0aa5fc414e69 | PASS |
| composer.lock SHA-256 | f15dde645598d805143d9ec1d3fab666730ac58ada078b0a3448c498bbd02be5 | PASS |
| Composer content-hash | b0b63cb6d58f91f89fdeb0bc86923263 | PASS |
| composer validate --strict | PASS | PASS |
| composer check-platform-reqs | PASS local | PARTIAL |
| package.json SHA-256 | 10a54d6736b26384ac68636e11f958360e6d18fb9c41e91e05707a918ad58622 | PASS |
| package-lock.json SHA-256 | 1a717514aba144013fe85101e951f18cc74de01f311c9f9b5378b767d00ed26a | PASS |
| npm lockfileVersion | 3 | PASS |
| npm dependency tree | npm ls --depth=0 PASS | PARTIAL |
| clean install | non exécuté en environnement vierge | MISSING |

Les locks sont exploitables, mais leur présence ne compense pas l'absence de commit candidat et de clean-room.

