# Provider Bindings

`SecurityComplianceRuntimeServiceProvider` est enregistré une seule fois dans `bootstrap/providers.php`.

| Abstraction | Implémentation | Portée |
|---|---|---|
| `SecurityComplianceOwnerSource` | `PostgreSqlSecurityComplianceOwnerSource` | singleton lazy |
| `SecurityComplianceRuntimeAvailabilityPolicy` | `DeterministicSecurityComplianceRuntimeAvailabilityPolicy` | singleton lazy |
| `SecurityComplianceRuntimeV1` | `DeterministicSecurityComplianceRuntime` | singleton lazy |

Le mapper et l'adapter PostgreSQL existants sont composés nominativement. Aucun alias ambigu ni résolution anticipée n'est introduit.
