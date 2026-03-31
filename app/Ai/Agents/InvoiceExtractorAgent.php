<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;

#[Provider('anthropic')]
#[Model('claude-sonnet-4-6')]
class InvoiceExtractorAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): string
    {
        return <<<'INSTRUCTIONS'
        Tu es un assistant spécialisé dans l'extraction de données depuis des photos de factures de moto.
        Analyse l'image fournie et extrais les informations suivantes avec la plus grande précision possible.

        Règles :
        - performed_at : date de l'intervention au format Y-m-d (ex: 2026-03-15). Si absente, utilise la date du document.
        - mileage : kilométrage du véhicule noté sur la facture (entier, sans unité). Null si absent.
        - garage : nom du garage ou de l'atelier. Null si absent.
        - total_amount : montant total TTC en euros (nombre décimal). Null si absent.
        - items : liste des prestations et pièces détachées avec leur libellé et montant. Au moins un item si des prestations sont visibles.
        - confidence : ton niveau de confiance global dans l'extraction (high = données claires, medium = quelques incertitudes, low = image floue ou données manquantes).
        INSTRUCTIONS;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'performed_at' => $schema->string()
                ->description('Date de l\'intervention au format Y-m-d'),
            'mileage' => $schema->integer()->nullable()
                ->description('Kilométrage relevé sur la facture'),
            'garage' => $schema->string()->nullable()
                ->description('Nom du garage ou de l\'atelier'),
            'total_amount' => $schema->number()->nullable()
                ->description('Montant total TTC en euros'),
            'items' => $schema->array(
                $schema->object([
                    'label'  => $schema->string()->description('Libellé de la prestation ou pièce'),
                    'amount' => $schema->number()->nullable()->description('Montant en euros'),
                ])
            )->description('Liste des prestations et pièces'),
            'confidence' => $schema->string()
                ->enum(['high', 'medium', 'low'])
                ->description('Niveau de confiance global dans l\'extraction'),
        ];
    }
}
