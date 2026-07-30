# Professional Status Event Routing Transaction and Recovery

Le repository ouvre une transaction uniquement lorsqu'aucune transaction externe n'existe. Il valide ou annule intégralement sa propre transaction et participe sans commit à une transaction appelante.

Un rejeu strictement identique retourne `AlreadyStored`. Toute divergence sous le même `messageId` retourne `Rejected` sans écrasement. Deux écritures concurrentes identiques convergent vers `Stored + AlreadyStored` et une seule ligne durable.
