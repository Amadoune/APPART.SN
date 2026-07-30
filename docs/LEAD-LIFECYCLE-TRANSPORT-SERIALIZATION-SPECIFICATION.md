# Lead Lifecycle Transport Serialization Specification

Le JSON conserve l'ordre fixe de l'enveloppe, puis `canonicalEvent`, puis `source → businessEventId → payloadChecksum`. Les slashs et caractères Unicode ne sont pas transformés.

Le transport ne recalcule jamais `eventId`. Il vérifie seulement que l'identité restaurée correspond aux deux occurrences certifiées dans l'enveloppe métier. Une modification de forme, d'ordre, d'identité ou de checksum est rejetée.

Toute évolution incompatible exige une nouvelle `transportVersion`. La version V1 demeure figée.
