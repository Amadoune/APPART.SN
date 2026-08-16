# Final store compatibility

Inspection read-only : `public_geography.decisions` existe et `payload` est `jsonb`. Le store contient actuellement zéro décision, dont zéro V2, conformément à l'absence de matérialisation.

La clé primaire `place_id` porte l'identité terminale. Les colonnes version, causalité, checksums et payload acceptent V2 sans changement de schéma. Le lookup descendant est fonctionnel par filtrage JSONB du tableau `revisionVector`, avec ordre déterministe `place_id ASC`; un index futur serait uniquement une optimisation.

L'Implementation doit élargir le contrat/mapper/writer actuellement V1 pour écrire V2 et faire distinguer au reader V2 Found/Unavailable/Missing/Corrupted. Elle doit préserver le reader V1 et rejeter tout schema inconnu. Aucune migration fonctionnelle n'est requise.
