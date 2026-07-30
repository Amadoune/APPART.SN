# Phase 5.2B — Media Ingestion — Discovery / Blueprint

## Statut

**GO CERTIFIÉ — FERMÉ.**

Ce jalon est exclusivement documentaire. Il ne crée ni code, ni contrat PHP,
ni migration, ni route, ni provider, ni événement concret, ni test.

## État réel

Le module Media possède déjà `MediaCollection`, `MediaItem`, leurs value
objects, leurs mutations, leur repository PostgreSQL et les migrations 004,
007, 031 et 032. F-06 protège le Media Item Lifecycle complet. F-11 consomme
une source Media durable pour la projection publique. F-15 protège les
fondations Delivery/Outbox.

La baseline ne possède pas de pipeline binaire certifié : réception sécurisée,
validation du contenu réel, stockage objet, scan, normalisation, variantes,
quotas, URLs temporaires et purge physique différée restent absents.

## Problème produit

L’authoring 5.2A peut décrire et soumettre une annonce, mais aucun parcours
certifié ne permet à son propriétaire de déposer une image et de la rendre
éligible au rattachement Media. La nouvelle capacité doit transformer un flux
binaire non fiable en asset validé sans attribuer à l’Ingestion l’autorité sur
`MediaCollection`, `MediaItem`, Property ou Listing.

## Autorités additives

1. **MediaUpload** : session d’upload, réservation, taille attendue, expiration
   et finalisation à usage unique.
2. **MediaAsset** : identité technique, checksum du contenu réel, type détecté,
   dimensions, object key opaque et état de sûreté.
3. **MediaProcessing** : scan, décodage, orientation, normalisation et variantes
   déterministes.
4. **MediaQuota** : réservations et consommation monotone par acteur et portée
   d’authoring.

`MediaCollection` et `MediaItem` restent exclusivement sous F-06.

## Parcours cible

1. une session IAM disponible et autorisée sur le Property/Listing demande une
   réservation d’upload ;
2. MediaQuota réserve atomiquement la capacité ;
3. MediaUpload délivre une cible opaque bornée en taille et en durée ;
4. le contenu reçu est finalisé une seule fois et mesuré côté serveur ;
5. MediaAsset calcule le checksum depuis les octets, détecte le type réel et
   place l’objet en quarantaine ;
6. MediaProcessing scanne, décode et produit les variantes déterministes ;
7. un asset sûr et complet devient éligible au handoff ;
8. une frontière publique F-06 rattache l’asset à une `MediaCollection` ;
9. les objets temporaires et les assets abandonnés suivent une purge différée.

## Invariants

- aucun octet non validé n’est public ;
- l’extension et le `Content-Type` client ne font jamais autorité ;
- checksum, dimensions et taille proviennent du contenu effectivement reçu ;
- les object keys sont opaques, non choisies par le client et sans PII ;
- une finalisation est à usage unique et idempotente ;
- une variante est dérivée d’un asset canonique immuable et d’une recette
  versionnée ;
- un objet en quarantaine n’est jamais servi depuis le domaine public ;
- la réservation de quota converge sous concurrence et est compensée à
  expiration ou échec terminal ;
- l’Ingestion n’écrit jamais dans les tables Media, Property, Listing ou IAM ;
- l’association à `MediaCollection` passe uniquement par un contrat public F-06.

## Formats et limites Discovery

Le MVP accepte uniquement des images JPEG, PNG et WebP statiques après
détection et décodage. GIF animé, SVG, vidéo, audio, document, archive et visite
virtuelle sont hors périmètre. Les limites chiffrées, recettes de variantes,
durées de rétention et quotas deviennent normatifs en Contracts Foundation.

## Handoff F-06

`CreateMediaCollection` et `AddMedia` sont des classes Application concrètes.
Elles dépendent de `MediaCollectionRegistry` et n’exposent ni commande V1, ni
résultat fermé, ni journal d’intents public. Elles ne peuvent donc pas être
consommées directement par 5.2B.

L’amendement suivant a été identifié par le Discovery puis ouvert par décision
d’autorité ; il demeure bloquant avant implémentation :

`A-5.2B-MEDIA-ATTACHMENT-BOUNDARY-01`.

Il devra définir une frontière publique versionnée et atomique de rattachement
sans modifier les invariants, transitions, événements V1, Runtime, HTTP,
Delivery, Outbox ou migrations de F-06.

## Hors périmètre

- modification de F-06, F-11, F-15, F-17, F-19 ou F-20 ;
- modération sémantique et décisions de contenu, réservées à 5.3 ;
- médias de profil professionnel, réservés à 5.2C ;
- migration des binaires Legacy, réservée à 5.7 ;
- CDN public définitif, effacement légal et anonymisation ;
- vidéo, audio, document et traitement génératif ;
- extension du catalogue Runtime Health.

## Décision d’autorité

**GO Discovery CERTIFIÉ**, avec une gate obligatoire : aucune implémentation ne
peut commencer avant certification de Contracts Foundation et traitement de
`A-5.2B-MEDIA-ATTACHMENT-BOUNDARY-01`.
