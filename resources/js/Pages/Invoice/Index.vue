<template>
    <div class="min-h-screen bg-gray-50 pb-8">
        <!-- Header -->
        <div class="bg-white shadow-sm px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a :href="route('dashboard')" class="text-gray-400 hover:text-gray-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-lg font-bold text-gray-900">Imports de factures</h1>
            </div>
            <a :href="route('invoice.create')"
                class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                + Importer
            </a>
        </div>

        <div class="px-4 mt-6 space-y-3 max-w-2xl mx-auto">
            <!-- Liste vide -->
            <div v-if="invoices.length === 0"
                class="bg-white rounded-xl shadow p-8 text-center text-sm text-gray-400">
                Aucun import pour le moment.
            </div>

            <!-- Cards -->
            <div v-for="invoice in invoices" :key="invoice.id"
                class="bg-white rounded-xl shadow p-4">
                <div class="flex items-start justify-between gap-3">
                    <!-- Infos -->
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">
                            {{ invoice.original_filename ?? 'Facture sans nom' }}
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ formatDate(invoice.created_at) }}</p>

                        <!-- Résumé extraction si done -->
                        <div v-if="invoice.summary" class="mt-2 text-xs text-gray-600 space-y-0.5">
                            <p v-if="invoice.summary.performed_at">📅 {{ invoice.summary.performed_at }}</p>
                            <p v-if="invoice.summary.garage">🏪 {{ invoice.summary.garage }}</p>
                            <p v-if="invoice.summary.total_amount">💶 {{ invoice.summary.total_amount }} €</p>
                        </div>
                    </div>

                    <!-- Statut -->
                    <div class="flex flex-col items-end gap-2 shrink-0">
                        <span class="text-xs font-medium px-2 py-1 rounded-full"
                            :class="statusClass(invoice.extraction_status)">
                            {{ statusLabel(invoice.extraction_status) }}
                        </span>

                        <!-- Badge confiance -->
                        <span v-if="invoice.summary?.confidence"
                            class="text-xs px-2 py-0.5 rounded-full"
                            :class="confidenceClass(invoice.summary.confidence)">
                            {{ confidenceLabel(invoice.summary.confidence) }}
                        </span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-2 mt-3 pt-3 border-t border-gray-100">
                    <!-- Voir (done) -->
                    <a v-if="invoice.extraction_status === 'done'"
                        :href="route('invoice.review', invoice.id)"
                        class="flex-1 text-center text-xs font-medium text-orange-600 border border-orange-200 rounded-lg py-1.5 hover:bg-orange-50 transition-colors">
                        Voir / Valider
                    </a>

                    <!-- Relancer (failed ou pending bloqué) -->
                    <button v-if="invoice.extraction_status === 'failed'"
                        type="button" @click="retry(invoice)"
                        :disabled="retryingId === invoice.id"
                        class="flex-1 text-xs font-medium text-blue-600 border border-blue-200 rounded-lg py-1.5 hover:bg-blue-50 disabled:opacity-50 transition-colors">
                        {{ retryingId === invoice.id ? 'Relance…' : 'Relancer' }}
                    </button>

                    <!-- Supprimer -->
                    <button type="button" @click="destroy(invoice)"
                        :disabled="deletingId === invoice.id"
                        class="flex-1 text-xs font-medium text-red-500 border border-red-200 rounded-lg py-1.5 hover:bg-red-50 disabled:opacity-50 transition-colors">
                        {{ deletingId === invoice.id ? 'Suppression…' : 'Supprimer' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    invoices: Array,
})

const retryingId = ref(null)
const deletingId = ref(null)

function retry(invoice) {
    retryingId.value = invoice.id
    useForm({}).post(route('invoice.retry', invoice.id), {
        onFinish: () => { retryingId.value = null },
    })
}

function destroy(invoice) {
    if (!confirm('Supprimer cet import ? Cette action est irréversible.')) return
    deletingId.value = invoice.id
    useForm({}).delete(route('invoice.destroy', invoice.id), {
        onFinish: () => { deletingId.value = null },
    })
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString('fr-FR', {
        day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit',
    })
}

function statusLabel(status) {
    return { pending: 'En attente', processing: 'Extraction…', done: 'Terminé', failed: 'Échoué' }[status] ?? status
}

function statusClass(status) {
    return {
        pending:    'bg-gray-100 text-gray-500',
        processing: 'bg-yellow-100 text-yellow-700',
        done:       'bg-green-100 text-green-700',
        failed:     'bg-red-100 text-red-600',
    }[status] ?? 'bg-gray-100 text-gray-500'
}

function confidenceLabel(c) {
    return { high: 'Confiance haute', medium: 'Confiance moyenne', low: 'Confiance faible' }[c] ?? c
}

function confidenceClass(c) {
    return {
        high:   'bg-green-50 text-green-600',
        medium: 'bg-yellow-50 text-yellow-600',
        low:    'bg-red-50 text-red-500',
    }[c] ?? ''
}
</script>
