# Successor Commit Policy

La candidate successor doit être matérialisée par un unique commit directement enfant de :

`ab5d3f57a577160d3aae36cee5778dc7bae59a16`.

Message normatif proposé :

`APPART.SN RC2 aligned successor candidate baseline r2`

L'alignement est préparé et validé dans un gate distinct, sans commit. Le gate de matérialisation crée ensuite l'unique commit contenant exactement le périmètre qualifié. Amend, rebase, merge parasite et réécriture sont interdits.
