# Lead Lifecycle Event Contract Analysis

## Décision

Le cycle certifié comporte quatre transitions mais trois faits métier. Les transitions `Delivered → Close → Closed` et `Rejected → Close → Closed` produisent toutes deux `lead.lifecycle.closed` : le fait est identique, tandis que l'origine reste portée sans ambiguïté par la transition exacte du payload.

Le catalogue est la seule correspondance transition–événement. Il refuse toute transition non certifiée et l'enveloppe vérifie que le type déclaré appartient bien à la transition transportée.

## Déterminisme

L'identité est un SHA-256 de valeurs exclusivement explicites : type, version du payload, `LeadId`, état précédent, action, état courant et version survenue. Les instants et l'acteur sont obligatoires mais ne sont ni générés ni utilisés pour masquer l'identité du fait.

La sérialisation fixe l'ordre de l'enveloppe, du payload et des métadonnées. Le même objet produit donc les mêmes octets.

## Confidentialité

Le payload applique une minimisation stricte. Il exclut les catalogues et preuves d'éligibilité ainsi que les coordonnées, contenus libres et identités Listing/Advertiser. L'acteur explicite reste une métadonnée d'audit contractuelle issue du contexte certifié 4.4D.

## Frontière

Cette fondation ne publie rien. Elle ne connaît ni transport, routeur, Inbox, Outbox, Consumer, Worker, HTTP, PostgreSQL ou Laravel. Les fondations 4.4A à 4.4D restent inchangées.
