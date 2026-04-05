<?php

use App\Ai\Agents\InvoiceExtractorAgent;
use App\Jobs\ProcessInvoiceJob;
use App\Models\Invoice;
use App\Models\Maintenance;
use App\Models\Motorcycle;
use App\Models\User;
use App\Services\InvoiceExtractionService;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    // Créer un fichier image factice pour les tests
    Storage::disk('public')->put('invoices/test/facture.jpg', 'fake-image-content');
});

$fakeExtraction = [
    'performed_at' => '2026-01-15',
    'mileage'      => 12500,
    'garage'       => 'Moto Shop Paris',
    'total_amount' => 150.00,
    'items'        => [
        ['label' => 'Vidange huile moteur', 'amount' => 120.00],
        ['label' => 'Filtre à huile', 'amount' => 30.00],
    ],
    'confidence'   => 'high',
];

// Extraction
test('l\'extraction met le statut à done et stocke les données', function () use ($fakeExtraction) {
    InvoiceExtractorAgent::fake([$fakeExtraction]);

    $invoice = Invoice::factory()->create([
        'path'              => 'invoices/test/facture.jpg',
        'extraction_status' => 'pending',
    ]);

    app(InvoiceExtractionService::class)->extract($invoice);

    $invoice->refresh();
    expect($invoice->extraction_status)->toBe('done');
    expect($invoice->extracted_data['performed_at'])->toBe('2026-01-15');
    expect($invoice->extracted_data['mileage'])->toBe(12500);
    expect($invoice->extracted_data['garage'])->toBe('Moto Shop Paris');
    expect($invoice->extracted_data['confidence'])->toBe('high');
    expect($invoice->extracted_data['items'])->toHaveCount(2);
});

test('le job dispatché exécute l\'extraction et passe à done', function () use ($fakeExtraction) {
    InvoiceExtractorAgent::fake([$fakeExtraction]);

    $invoice = Invoice::factory()->create([
        'path'              => 'invoices/test/facture.jpg',
        'extraction_status' => 'pending',
    ]);

    ProcessInvoiceJob::dispatch($invoice);

    expect($invoice->fresh()->extraction_status)->toBe('done');
});

test('l\'extraction passe à failed si l\'agent échoue', function () {
    InvoiceExtractorAgent::fake(function () {
        throw new \RuntimeException('API error');
    });

    $invoice = Invoice::factory()->create([
        'path'              => 'invoices/test/facture.jpg',
        'extraction_status' => 'pending',
    ]);

    app(InvoiceExtractionService::class)->extract($invoice);

    expect($invoice->fresh()->extraction_status)->toBe('failed');
});

test('le job ProcessInvoiceJob appelle bien le service d\'extraction', function () use ($fakeExtraction) {
    InvoiceExtractorAgent::fake([$fakeExtraction]);

    $invoice = Invoice::factory()->create([
        'path'              => 'invoices/test/facture.jpg',
        'extraction_status' => 'pending',
    ]);

    (new ProcessInvoiceJob($invoice))->handle(app(InvoiceExtractionService::class));

    expect($invoice->fresh()->extraction_status)->toBe('done');
    expect($invoice->fresh()->extracted_data)->not->toBeNull();
});

// Page review
test('la page review est accessible quand l\'extraction est done', function () {
    Motorcycle::factory()->create();

    $invoice = Invoice::factory()->create([
        'extraction_status' => 'done',
        'extracted_data'    => ['performed_at' => '2026-01-15', 'confidence' => 'high'],
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('invoice.review', $invoice))
        ->assertStatus(200)
        ->assertInertia(fn($page) => $page->component('Invoice/Review'));
});

// Confirmation
test('la confirmation crée une maintenance avec ses items et lie la facture', function () {
    $motorcycle = Motorcycle::factory()->create();

    $invoice = Invoice::factory()->create([
        'extraction_status' => 'done',
        'extracted_data'    => [],
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('invoice.confirm', $invoice), [
            'performed_at' => '2026-01-15',
            'mileage'      => 12500,
            'garage'       => 'Moto Shop Paris',
            'total_amount' => 150.00,
            'notes'        => 'RAS',
            'items'        => [
                ['label' => 'Vidange huile moteur', 'amount' => 120.00],
                ['label' => 'Filtre à huile', 'amount' => 30.00],
            ],
        ])
        ->assertRedirect();

    $maintenance = Maintenance::first();
    expect($maintenance)->not->toBeNull();
    expect($maintenance->mileage)->toBe(12500);
    expect($maintenance->garage)->toBe('Moto Shop Paris');
    expect($maintenance->notes)->toBe('RAS');
    expect($maintenance->maintenanceItems)->toHaveCount(2);

    expect($invoice->fresh()->maintenance_id)->toBe($maintenance->id);
});

test('la confirmation échoue sans les champs obligatoires', function () {
    Motorcycle::factory()->create();

    $invoice = Invoice::factory()->create([
        'extraction_status' => 'done',
        'extracted_data'    => [],
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('invoice.confirm', $invoice), [])
        ->assertSessionHasErrors(['performed_at', 'items']);
});

test('la confirmation échoue si un item n\'a pas de label', function () {
    Motorcycle::factory()->create();

    $invoice = Invoice::factory()->create([
        'extraction_status' => 'done',
        'extracted_data'    => [],
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('invoice.confirm', $invoice), [
            'performed_at' => '2026-01-15',
            'mileage'      => 12500,
            'items'        => [['label' => '', 'amount' => null]],
        ])
        ->assertSessionHasErrors(['items.0.label']);
});
