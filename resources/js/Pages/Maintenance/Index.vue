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
                <h1 class="text-lg font-bold text-gray-900">Historique</h1>
            </div>
            <a :href="route('maintenance.create')"
                class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Ajouter
            </a>
        </div>

        <div class="px-4 mt-6 max-w-2xl mx-auto">
            <!-- Vide -->
            <div v-if="maintenances.length === 0" class="bg-white rounded-xl shadow p-8 text-center">
                <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p class="text-gray-400 text-sm">Aucune intervention enregistrée</p>
                <a :href="route('maintenance.create')"
                    class="mt-4 inline-block text-orange-500 hover:text-orange-700 text-sm font-medium">
                    Ajouter la première
                </a>
            </div>

            <!-- Liste -->
            <div v-else class="space-y-3">
                <a v-for="maintenance in maintenances" :key="maintenance.id"
                    :href="route('maintenance.show', maintenance.id)"
                    class="bg-white rounded-xl shadow p-4 block hover:shadow-md transition-shadow">
                    <!-- En-tête de la card -->
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">
                                {{ formatDate(maintenance.performed_at) }}
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ maintenance.mileage ?
                                    `${maintenance.mileage.toLocaleString('fr-FR')} km` :
                                    'Pas de km renseigné'
                                }}
                                <span v-if="maintenance.garage"> · {{ maintenance.garage }}</span>
                            </p>
                        </div>
                        <div class="text-right">
                            <p v-if="totalAmount(maintenance.maintenance_items)"
                                class="text-sm font-semibold text-gray-900">
                                {{ totalAmount(maintenance.maintenance_items) }} €
                            </p>
                        </div>
                    </div>

                    <!-- Items -->
                    <ul class="space-y-1">
                        <li v-for="item in maintenance.maintenance_items" :key="item.id"
                            class="flex justify-between text-xs text-gray-600">
                            <span>{{ item.label }}</span>
                            <span v-if="item.amount" class="text-gray-400">{{ Number(item.amount).toFixed(2) }} €</span>
                        </li>
                    </ul>
                </a>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useFormatters } from '@/composables/useFormatters'

const props = defineProps({
    motorcycle: Object,
    maintenances: Array,
})

const { formatDate, totalAmount } = useFormatters()
</script>
