<?php

use App\Jobs\ProcessInvoiceJob;
use App\Models\Invoice;
use App\Models\Motorcycle;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('private');
    Queue::fake();
});

// Page
test('la page upload est accessible', function () {
    Motorcycle::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('invoice.create'))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page->component('Invoice/Create'));
});

// Upload
test('une photo JPG valide peut être uploadée', function () {
    $motorcycle = Motorcycle::factory()->create();

    $file = UploadedFile::fake()->image('facture.jpg', 800, 600);

    $this->actingAs(User::factory()->create())
        ->post(route('invoice.store'), ['photo' => $file])
        ->assertRedirect();

    $invoice = Invoice::first();
    expect($invoice)->not->toBeNull();
    expect($invoice->extraction_status)->toBe('pending');
    expect($invoice->maintenance_id)->toBeNull();

    Storage::disk('private')->assertExists($invoice->path);
});

test('une photo PNG valide peut être uploadée', function () {
    Motorcycle::factory()->create();

    $file = UploadedFile::fake()->image('facture.png', 800, 600);

    $this->actingAs(User::factory()->create())
        ->post(route('invoice.store'), ['photo' => $file])
        ->assertRedirect();

    expect(Invoice::count())->toBe(1);
});

test('le job ProcessInvoiceJob est dispatché après upload', function () {
    Motorcycle::factory()->create();

    $file = UploadedFile::fake()->image('facture.jpg');

    $this->actingAs(User::factory()->create())
        ->post(route('invoice.store'), ['photo' => $file]);

    Queue::assertPushed(ProcessInvoiceJob::class);
});

test('un fichier sans photo est refusé', function () {
    Motorcycle::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('invoice.store'), [])
        ->assertSessionHasErrors(['photo']);
});

test('un fichier PDF est refusé', function () {
    Motorcycle::factory()->create();

    $file = UploadedFile::fake()->create('facture.pdf', 100, 'application/pdf');

    $this->actingAs(User::factory()->create())
        ->post(route('invoice.store'), ['photo' => $file])
        ->assertSessionHasErrors(['photo']);
});

test('un fichier de plus de 10 Mo est refusé', function () {
    Motorcycle::factory()->create();

    $file = UploadedFile::fake()->image('facture.jpg')->size(11000);

    $this->actingAs(User::factory()->create())
        ->post(route('invoice.store'), ['photo' => $file])
        ->assertSessionHasErrors(['photo']);
});

// Page processing
test('la page processing est accessible', function () {
    Motorcycle::factory()->create();
    $invoice = Invoice::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('invoice.processing', $invoice))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page->component('Invoice/Processing'));
});
