# Blade audit

V1 rend chaque item comme lien. V2 rend : `<nav aria-label="Fil d'Ariane"><ol><li><span>label</span></li>…</ol></nav>`.

Le terminal reçoit `aria-current="location"` ou, si non supporté par le renderer, `aria-current="page"`; aucun href. La canonical de l'annonce reste dans `<link rel=canonical>` et n'est pas un item Geography.
