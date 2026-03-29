<template>
    <div class="min-h-screen bg-gray-50 pb-8">
        <!-- Header -->
        <div class="bg-white shadow-sm px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a :href="route('maintenance.index')" class="text-gray-400 hover:text-gray-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-lg font-bold text-gray-900">Détail de l'intervention</h1>
            </div>
            <div class="flex gap-2">
                <a href="#"
                    class="text-sm text-orange-500 hover:text-orange-700 font-medium px-3 py-1.5 rounded-lg border border-orange-200 hover:border-orange-400 transition-colors opacity-50 pointer-events-none">
                    Modifier
                </a>
                <button @click="confirmDelete"
                    class="text-sm text-red-400 hover:text-red-600 font-medium px-3 py-1.5 rounded-lg border border-red-200 hover:border-red-400 transition-colors">
                    Supprimer
                </button>
            </div>
        </div>

        <div class="px-4 mt-6 space-y-4 max-w-2xl mx-auto">
            <!-- Infos principales -->
            <div class="bg-white rounded-xl shadow p-5 space-y-3">
                <div class="flex justify-between">
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide">Date</p>
                        <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ formatDate(maintenance.performed_at) }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-400 uppercase tracking-wide">Kilométrage</p>
                        <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ maintenance.mileage.toLocaleString('fr-FR') }} km</p>
                    </div>
                </div>

                <div v-if="maintenance.garage">
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Garage</p>
                    <p class="text-sm text-gray-900 mt-0.5">{{ maintenance.garage }}</p>
                </div>

                <div v-if="maintenance.notes">
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Notes</p>
                    <p class="text-sm text-gray-700 mt-0.5 whitespace-pre-line">{{ maintenance.notes }}</p>
                </div>
            </div>

            <!-- Prestations -->
            <div class="bg-white rounded-xl shadow p-5">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-3">Prestations</p>
                <ul class="space-y-2">
                    <li v-for="item in maintenance.maintenance_items" :key="item.id"
                        class="flex justify-between text-sm">
                        <span class="text-gray-800">{{ item.label }}</span>
                        <span v-if="item.amount" class="text-gray-500 font-medium">
                            {{ formatAmount(item.amount) }} €
                        </span>
                    </li>
                </ul>
                <div v-if="total" class="mt-3 pt-3 border-t border-gray-100 flex justify-between text-sm font-semibold">
                    <span class="text-gray-700">Total</span>
                    <span class="text-gray-900">{{ total }} €</span>
                </div>
            </div>

            <!-- Photos factures -->
            <div v-if="maintenance.invoices && maintenance.invoices.length > 0" class="bg-white rounded-xl shadow p-5">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-3">Factures</p>
                <div class="grid grid-cols-2 gap-2">
                    <a v-for="invoice in maintenance.invoices" :key="invoice.id"
                        :href="'/storage/' + invoice.file_path" target="_blank">
                        <img :src="'/storage/' + invoice.file_path"
                            class="w-full h-32 object-cover rounded-lg border border-gray-100" />
                    </a>
                </div>
            </div>
        </div>

        <!-- Modal confirmation suppression -->
        <div v-if="showDeleteModal"
            class="fixed inset-0 bg-black/50 flex items-end justify-center z-50 p-4">
            <div class="bg-white rounded-2xl p-6 w-full max-w-sm">
                <h2 class="text-base font-bold text-gray-900 mb-2">Supprimer l'intervention ?</h2>
                <p class="text-sm text-gray-500 mb-5">Cette action est irréversible.</p>
                <div class="flex gap-3">
                    <button @click="showDeleteModal = false"
                        class="flex-1 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                        Annuler
                    </button>
                    <button @click="deleteConfirmed"
                        class="flex-1 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm font-medium transition-colors">
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useFormatters } from '@/composables/useFormatters'

const props = defineProps({
    motorcycle: Object,
    maintenance: Object,
})

const { formatDate, formatAmount, totalAmount } = useFormatters()

const total = computed(() => totalAmount(props.maintenance.maintenance_items))

const showDeleteModal = ref(false)
const deleteForm = useForm({})

function confirmDelete() {
    showDeleteModal.value = true
}

function deleteConfirmed() {
    // Route maintenance.destroy disponible avec SCRUM-17
    showDeleteModal.value = false
}
</script>
