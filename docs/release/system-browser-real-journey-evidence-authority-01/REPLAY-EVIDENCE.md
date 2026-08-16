# Preuve de replay

Pour Claim, BeginReview, Approve et Projection lorsque le contrat l'exige, la première commande est enregistrée dans le manifest avant son émission :

- même `commandId` ;
- même `occurredAt` ;
- même ressource et actor ;
- même expectedVersion ou valeur canonique attendue par le contrat ;
- même payload canonique et checksum/source revision.

Le replay utilise le même formulaire productif ou le mécanisme productif explicitement prévu. Il ne recrée aucun instant. Le résultat attendu est `AlreadyApplied` ou l'idempotence fermée historiquement certifiée.

La preuve vérifie aussi l'absence de seconde mutation, de seconde ligne ledger et de duplication du read model.
