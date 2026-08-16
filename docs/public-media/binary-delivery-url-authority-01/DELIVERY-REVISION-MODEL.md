# Delivery revision model

La révision binaire est `assetVersion`, certifiée avec `contentChecksum`. Elle change pour toute transition d'asset pertinente; un contenu divergent sous la même identité est rejeté.

Order, primary et caption changent la version Public Media, pas la delivery revision. Attachment/retrait/publication modifient l'éligibilité et déclenchent refresh/révocation, sans réécrire le binaire.
