# Session Expiry

Parcours normatif :

`draft persistant → expiration idle → Login réel → Portfolio owner-scoped → sélection du même listingId → Resume Reader → workspace réhydraté`.

La session porte uniquement l’identité et le contexte IAM. Elle ne porte jamais les champs du draft, les versions, le step ou les IDs métier.

Le paramètre `next` peut conserver une destination locale de reprise, par exemple `/authoring/workspace/{listingId}`, mais ne vaut pas autorisation. Après Login, IAM et ownership sont intégralement revérifiés.

Une session expirée pendant la reprise retourne `401`. Après réauthentification, la même URL peut être rejouée en lecture seule sans générer de nouvelles identités.
