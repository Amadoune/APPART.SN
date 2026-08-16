# Command Identity

Pour cette opération sans ledger dédié, `generationId` est aussi l'identité idempotente de commande. Aucun `commandId` distinct n'est requis: Manager et état durable classent les replays. La commande reçoit obligatoirement le même generationId et le même scope canonique. `occurredAt` et checksum de commande ne font pas partie des contrats existants; les preuves d'exploitation portent l'horodatage externe.

Une réinvocation avec une autre identité n'est pas un replay et doit être refusée si une Candidate du bootstrap ou une Active existe.
