# Validator Audit

Le Validator relit tous les records de la génération, remappe et vérifie leurs checksums, compte les records `current`, exige chaque Listing du manifeste, compare exactement les watermarks et classe missing/divergent/corrupt. Un record courant non déclaré est divergent. `isValid` exige expected=observed et trois listes d'anomalies vides. Le manifeste doit donc couvrir exactement la Candidate.
