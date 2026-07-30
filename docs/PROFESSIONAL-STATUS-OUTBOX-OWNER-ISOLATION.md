# Professional Status Outbox Owner Isolation

Le Writer résout le schéma depuis `sourceModule`. Le Reader parcourt les owners reconnus, restaure le module depuis le schéma et exige `m.source_module = :owner_module`.

Une ligne copiée dans un mauvais schéma ne peut donc jamais être restaurée comme message `Professionals`. Les sept owners antérieurs restent indépendants.
