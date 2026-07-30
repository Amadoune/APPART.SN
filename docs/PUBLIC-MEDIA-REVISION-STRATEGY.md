# Stratégie de révision Public Media

## Entrées normatives

Une révision nécessite :

1. une séquence source positive et explicite ;
2. un payload Media public canonique non vide ;
3. une clé de causalité non vide.

À entrées identiques, version, checksum et causalité sont strictement identiques. Le checksum porte sur l’intégralité du payload canonique, notamment l’ordre déjà décidé des médias publics.

## Responsabilités

La fondation ne détermine ni la couverture, ni l’ordre, ni l’éligibilité, ni les variantes publiques. Ces décisions doivent être terminées avant la construction de la révision. Elle certifie uniquement l’identité stable et versionnée du résultat public.

La séquence source devient la version Public Media du watermark. Aucun timestamp, compteur local implicite ou checksum tronqué ne peut servir de version.

## Compatibilité Projection

`PublicMediaRevision::watermarkVersion()` fournit directement `publicMediaVersion`. Avec une révision Public Geography également présente, le watermark existant devient `Ready` sans modification du Store ou de l’Updater.
