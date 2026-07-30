# Phase 4.7C-R1 — Contextual Replay Inspection

Le port additif `AdministrativeActionContextualReplayInspector` expose uniquement :

```text
inspectLatest(AdministrativeActionId)
→ Found | Missing | Corrupted
```

`Found` porte obligatoirement :

* l'identité de l'action administrative ;
* la version exacte ;
* la transition exacte ;
* le contexte V1 exact ;
* son checksum contextuel.

`Missing` et `Corrupted` ne portent aucun snapshot. Une absence ne devient jamais une corruption et une corruption ne devient jamais une absence ou un résultat métier.

Aucune implémentation, persistance, migration ou composition Runtime n'appartient à ce sprint.
