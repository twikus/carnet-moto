<template>
    <div class="min-h-screen bg-gray-50 pb-8">
        <!-- Header -->
        <div class="bg-white shadow-sm px-4 py-4 flex items-center gap-3">
            <a :href="route('invoice.index')" class="text-gray-400 hover:text-gray-600 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h1 class="text-lg font-bold text-gray-900">Vérifier la facture</h1>
        </div>

        <div class="px-4 mt-6 max-w-2xl mx-auto space-y-4">
            <!-- Image de la facture -->
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <img :src="route('invoice.image', invoice.id)" :alt="invoice.original_filename"
                    class="w-full object-contain max-h-72" />
            </div>

            <!-- Badge confiance -->
            <div v-if="confidence" class="flex items-center gap-2">
                <span class="text-xs px-2.5 py-1 rounded-full font-medium" :class="confidenceClass">
                    {{ confidenceLabel }}
                </span>
                <span class="text-xs text-gray-400">Niveau de confiance de l'extraction IA</span>
            </div>

            <!-- Formulaire -->
            <form @submit.prevent="submit" class="space-y-4">
                <!-- Date + Kilométrage -->
                <div class="bg-white rounded-xl shadow p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Date de l'intervention *</label>
                        <input type="date" v-model="form.performed_at"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
                            :class="{ 'border-red-400': errors.performed_at }" />
                        <p v-if="errors.performed_at" class="text-xs text-red-500 mt-1">{{ errors.performed_at }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Kilométrage *</label>
                        <input type="number" v-model="form.mileage" min="0"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
                            :class="{ 'border-red-400': errors.mileage }" />
                        <p v-if="errors.mileage" class="text-xs text-red-500 mt-1">{{ errors.mileage }}</p>
                    </div>
                </div>

                <!-- Garage + Montant -->
                <div class="bg-white rounded-xl shadow p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Garage</label>
                        <input type="text" v-model="form.garage" placeholder="Nom du garage"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Montant total TTC (€)</label>
                        <input type="number" v-model="form.total_amount" step="0.01" min="0" placeholder="0.00"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400" />
                    </div>
                </div>

                <!-- Items -->
                <div class="bg-white rounded-xl shadow p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Prestations *</p>
                        <button type="button" @click="addItem"
                            class="text-xs text-orange-600 font-medium hover:text-orange-700">
                            + Ajouter
                        </button>
                    </div>

                    <p v-if="errors.items" class="text-xs text-red-500 mb-2">{{ errors.items }}</p>

                    <div class="space-y-2">
                        <div v-for="(item, index) in form.items" :key="index"
                            class="flex items-center gap-2">
                            <input type="text" v-model="item.label" placeholder="Libellé"
                                class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
                                :class="{ 'border-red-400': errors[`items.${index}.label`] }" />
                            <input type="number" v-model="item.amount" step="0.01" min="0" placeholder="€"
                                class="w-24 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400" />
                            <button v-if="form.items.length > 1" type="button" @click="removeItem(index)"
                                class="text-gray-300 hover:text-red-400 shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="bg-white rounded-xl shadow p-5">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Notes libres</label>
                    <textarea v-model="form.notes" rows="3" placeholder="Observations, remarques…"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 resize-none"></textarea>
                </div>

                <!-- Submit -->
                <button type="submit" :disabled="form.processing"
                    class="w-full bg-orange-500 hover:bg-orange-600 disabled:opacity-50 text-white font-medium py-3 rounded-xl text-sm transition-colors">
                    {{ form.processing ? 'Enregistrement…' : 'Valider et enregistrer' }}
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    invoice: Object,
})

const data = props.invoice.extracted_data ?? {}
const confidence = data.confidence ?? null

const form = useForm({
    performed_at: data.performed_at ?? '',
    mileage:      data.mileage ?? '',
    garage:       data.garage ?? '',
    total_amount: data.total_amount ?? '',
    notes:        '',
    items:        data.items?.length
        ? data.items.map(i => ({ label: i.label, amount: i.amount ?? '' }))
        : [{ label: '', amount: '' }],
})

const errors = computed(() => form.errors)

function addItem() {
    form.items.push({ label: '', amount: '' })
}

function removeItem(index) {
    form.items.splice(index, 1)
}

function submit() {
    form.post(route('invoice.confirm', props.invoice.id))
}

const confidenceClass = computed(() => ({
    'bg-green-50 text-green-600':  confidence === 'high',
    'bg-yellow-50 text-yellow-600': confidence === 'medium',
    'bg-red-50 text-red-500':      confidence === 'low',
}))

const confidenceLabel = computed(() => ({
    high:   'Confiance haute',
    medium: 'Confiance moyenne',
    low:    'Confiance faible',
}[confidence] ?? confidence))
</script>
