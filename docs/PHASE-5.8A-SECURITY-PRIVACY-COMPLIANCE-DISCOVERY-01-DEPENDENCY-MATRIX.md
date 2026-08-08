# Dependency Matrix — Security, Privacy & Compliance

| Dépendance candidate | Usage autorisé à qualifier | Limite d'autorité |
|---|---|---|
| owners métier certifiés | classification, finalité, rétention et décisions de domaine | aucune substitution métier |
| Identity & Access gelé | identité et autorisation via surfaces publiques certifiées | aucune modification ni accès interne |
| Administration Console gelée | observation publique des états administratifs certifiés | aucune commande implicite |
| Legacy Migration gelée | preuves et statuts publics certifiés | aucune réouverture ou écriture Legacy |
| gestionnaire de secrets / KMS candidat | stockage, rotation et opérations cryptographiques futures | fournisseur non retenu à ce stade |
| horloge UTC fiable candidate | horodatage des preuves et incidents | aucune autorité métier |
| stockage d'audit immuable candidat | preuves append-only et contrôles d'intégrité | contenu minimisé, accès restreint |
| fonction juridique / conformité | validation d'applicabilité et d'obligations | aucune implémentation technique directe |

Sont interdits pendant ce Discovery : dépendances code, Provider, Runtime, Persistence, HTTP, Event, Delivery, Outbox, SQL, migration, Transport, Routing et Consumer.

Aucune capacité 5.1 à 5.7 n'est modifiée. Les dépendances candidates ne deviennent pas autorisées sans Foundation explicitement ouverte.
