# Issuance Timing

| Option | Évaluation | Décision |
|---|---|---|
| Pendant Authoring | Crée prématurément une identité Domain et couple le draft | Rejetée |
| Pendant PromoteAuthoredPropertyV1 | Version et intention stables disponibles, retry déterministe | **Retenue** |
| Dans RegisterProperty | Étendrait sa responsabilité et sa signature | Rejetée |
| Dans Address | Introduirait infrastructure/identité dans le modèle | Rejetée |

L'émission pure précède immédiatement `new Address(...)`. Un draft abandonné ne matérialise aucun AddressId Domain. Un échec ultérieur réémet exactement la même valeur au retry.
