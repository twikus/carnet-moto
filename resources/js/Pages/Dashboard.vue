<template>
    <div class="min-h-screen bg-gray-50 pb-8">
        <!-- Header -->
        <div class="bg-white shadow-sm px-4 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-gray-900">{{ motorcycle.make }} {{ motorcycle.model }}</h1>
                <p class="text-xs text-gray-400">{{ motorcycle.year }} · {{ motorcycle.plate ?? 'Sans immatriculation'
                    }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a :href="route('settings')"
                    class="text-sm text-gray-400 hover:text-gray-600 p-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </a>
                <form method="POST" action="/logout" @submit.prevent="logout">
                    <button type="submit" class="text-sm text-gray-400 hover:text-gray-600 p-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        <div class="px-4 mt-6 space-y-4 max-w-2xl mx-auto">
            <!-- Kilométrage actuel -->
            <div class="bg-white rounded-xl shadow p-5 flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Kilométrage actuel</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">
                        {{ dashboard.currentMileage.toLocaleString('fr-FR') }}
                        <span class="text-base font-normal text-gray-400">km</span>
                    </p>
                </div>
                <button
                    class="bg-orange-50 text-orange-600 hover:bg-orange-100 text-sm font-medium px-3 py-2 rounded-lg transition-colors">
                    Mettre à jour
                </button>
            </div>

            <!-- Dernière intervention -->
            <div class="bg-white rounded-xl shadow p-5">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-3">Dernière intervention</p>
                <div v-if="dashboard.lastMaintenance">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex flex-wrap gap-1.5">
                                <span v-for="label in dashboard.lastMaintenance.labels" :key="label"
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-50 text-orange-700">
                                    {{ label }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 mt-2">
                                {{ formatDate(dashboard.lastMaintenance.performed_at) }}
                                · {{ dashboard.lastMaintenance.mileage.toLocaleString('fr-FR') }} km
                                <span v-if="dashboard.lastMaintenance.garage"> · {{ dashboard.lastMaintenance.garage
                                    }}</span>
                            </p>
                        </div>
                    </div>
                </div>
                <p v-else class="text-sm text-gray-400 italic">Aucune intervention enregistrée</p>
            </div>

            <!-- Raccourcis -->
            <div class="grid grid-cols-2 gap-3">
                <a :href="route('maintenance.create')"
                    class="bg-orange-500 hover:bg-orange-600 text-white font-medium py-3 px-4 rounded-xl text-sm transition-colors flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Ajouter une intervention
                </a>
                <a :href="route('maintenance.index')"
                    class="bg-white hover:bg-gray-50 text-gray-700 font-medium py-3 px-4 rounded-xl text-sm border border-gray-200 transition-colors flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Voir l'historique
                </a>
            </div>

            <!-- Widget entretiens à venir -->
            <div class="bg-white rounded-xl shadow p-5">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-3">Entretiens à venir</p>

                <div v-if="dashboard.upcomingMaintenances.length === 0" class="text-sm text-gray-400 italic">
                    Aucun type d'entretien configuré.
                </div>

                <div v-else class="space-y-3">
                    <div v-for="item in dashboard.upcomingMaintenances" :key="item.id"
                        class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0" :class="statusColor(item.status)"></span>
                            <span class="text-sm text-gray-700">{{ item.name }}</span>
                        </div>
                        <div class="text-right">
                            <span v-if="item.km_left !== null" class="text-xs text-gray-500">
                                {{ item.km_left > 0 ? `${item.km_left.toLocaleString('fr-FR')} km` : 'Dépassé' }}
                            </span>
                            <span v-if="item.km_left !== null && item.days_left !== null"
                                class="text-xs text-gray-300 mx-1">·</span>
                            <span v-if="item.days_left !== null" class="text-xs text-gray-500">
                                {{ item.days_left > 0 ? `${item.days_left}j` : 'Dépassé' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { router, usePage } from '@inertiajs/vue3'

const props = defineProps({
    motorcycle: Object,
    dashboard: Object,
});

const { props: pageProps } = usePage();

function formatDate(date) {
    return new Date(date).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
}

function statusColor(status) {
    return {
        'bg-green-500': status === 'green',
        'bg-orange-400': status === 'orange',
        'bg-red-500': status === 'red',
    };
}

function logout() {
    router.post('/logout');
}
</script>
