# Ordering model

L'ordre normatif est `MediaItem.order`, persisté en `media_order`. Le repository relit explicitement `ORDER BY media_order, media_id`; l'unicité de l'ordre des items actifs est garantie en base.

Un ordre SQL implicite ou l'ordre d'upload ne constitue pas une autorité. `MediaReordered` et la version de collection capturent un changement.
