# Professional Status Contextual Replay Inspection Specification

Le port additif `ProfessionalStatusContextualReplayInspector` expose uniquement :

```text
inspectLatest(ProfessionalStatusId)
→ ProfessionalStatusContextualInspectionResult
```

Le résultat est fermé :

| Statut | Snapshot | Signification |
|---|---|---|
| `Found` | obligatoire | dernier append exact et intègre |
| `Missing` | interdit | aucun append contextuel inspectable |
| `Corrupted` | interdit | donnée présente mais contractuellement invalide |

Le snapshot immuable contient l'identité, la version exacte, la transition exacte, l'acteur, `occurredAt` et le checksum contextuel. Le port ne reconstruit et n'interprète jamais une transition.
