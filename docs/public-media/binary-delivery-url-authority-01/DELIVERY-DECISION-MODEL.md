# Delivery decision model

Option retenue : **B**, locator public stable dans un contrat PublicMediaItem V2, URL absolue résolue au read-time.

Aucune table `DeliveryDecision` distincte n'est nécessaire. L'autorité retourne un résultat fermé portant locator, MediaId, assetVersion, MIME et checksum seulement lorsqu'ils sont admissibles.
