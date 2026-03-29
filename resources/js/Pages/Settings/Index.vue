<template>
    <div class="min-h-screen bg-gray-50 pb-8">
        <!-- Header -->
        <div class="bg-white shadow-sm px-4 py-4 flex items-center gap-3">
            <a :href="route('dashboard')" class="text-gray-400 hover:text-gray-600 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h1 class="text-lg font-bold text-gray-900">Paramètres</h1>
        </div>

        <!-- Flash messages -->
        <div v-if="$page.props.flash?.success" class="mx-4 mt-4 max-w-2xl mx-auto bg-green-50 border border-green-200 text-green-800 text-sm rounded-xl px-4 py-3">
            {{ $page.props.flash.success }}
        </div>
        <div v-if="$page.props.flash?.error" class="mx-4 mt-4 max-w-2xl mx-auto bg-red-50 border border-red-200 text-red-800 text-sm rounded-xl px-4 py-3">
            {{ $page.props.flash.error }}
        </div>

        <div class="px-4 mt-6 space-y-6 max-w-2xl mx-auto">

            <!-- Section Moto -->
            <div class="bg-white rounded-xl shadow p-5">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Ma moto</h2>
                <form @submit.prevent="submitMotorcycle" class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Marque *</label>
                            <input v-model="motoForm.make" type="text"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
                                :class="{ 'border-red-400': motoForm.errors.make }" />
                            <p v-if="motoForm.errors.make" class="text-xs text-red-500 mt-1">{{ motoForm.errors.make }}</p>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Modèle *</label>
                            <input v-model="motoForm.model" type="text"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
                                :class="{ 'border-red-400': motoForm.errors.model }" />
                            <p v-if="motoForm.errors.model" class="text-xs text-red-500 mt-1">{{ motoForm.errors.model }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Année *</label>
                            <input v-model="motoForm.year" type="number"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
                                :class="{ 'border-red-400': motoForm.errors.year }" />
                            <p v-if="motoForm.errors.year" class="text-xs text-red-500 mt-1">{{ motoForm.errors.year }}</p>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Immatriculation</label>
                            <input v-model="motoForm.plate" type="text"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Kilométrage initial *</label>
                        <input v-model="motoForm.initial_mileage" type="number" min="0"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
                            :class="{ 'border-red-400': motoForm.errors.initial_mileage }" />
                        <p v-if="motoForm.errors.initial_mileage" class="text-xs text-red-500 mt-1">{{ motoForm.errors.initial_mileage }}</p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Photo</label>
                        <input type="file" accept="image/jpeg,image/png" @change="motoForm.photo = $event.target.files[0]"
                            class="w-full text-sm text-gray-500 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-orange-50 file:text-orange-700" />
                        <p v-if="motoForm.errors.photo" class="text-xs text-red-500 mt-1">{{ motoForm.errors.photo }}</p>
                    </div>
                    <button type="submit" :disabled="motoForm.processing"
                        class="w-full bg-orange-500 hover:bg-orange-600 disabled:opacity-50 text-white font-medium py-2.5 px-4 rounded-xl text-sm transition-colors">
                        Enregistrer
                    </button>
                </form>
            </div>

            <!-- Section Discord -->
            <div class="bg-white rounded-xl shadow p-5">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Webhook Discord</h2>
                <form @submit.prevent="submitDiscord" class="space-y-3">
                    <div>
                        <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">URL du webhook</label>
                        <input v-model="discordForm.discord_webhook_url" type="url" placeholder="https://discord.com/api/webhooks/..."
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
                            :class="{ 'border-red-400': discordForm.errors.discord_webhook_url }" />
                        <p v-if="discordForm.errors.discord_webhook_url" class="text-xs text-red-500 mt-1">{{ discordForm.errors.discord_webhook_url }}</p>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" :disabled="discordForm.processing"
                            class="flex-1 bg-orange-500 hover:bg-orange-600 disabled:opacity-50 text-white font-medium py-2.5 px-4 rounded-xl text-sm transition-colors">
                            Enregistrer
                        </button>
                        <button type="button" :disabled="!discordForm.discord_webhook_url || testForm.processing" @click="testDiscord"
                            class="flex-1 bg-indigo-500 hover:bg-indigo-600 disabled:opacity-50 text-white font-medium py-2.5 px-4 rounded-xl text-sm transition-colors">
                            Tester l'envoi
                        </button>
                    </div>
                </form>
            </div>

            <!-- Section Types d'entretien -->
            <div class="bg-white rounded-xl shadow p-5">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Types d'entretien</h2>

                <!-- Liste -->
                <div class="space-y-2 mb-4">
                    <div v-for="type in maintenanceTypes" :key="type.id">
                        <!-- Affichage normal -->
                        <div v-if="editingId !== type.id" class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2.5">
                            <div class="flex-1 min-w-0">
                                <span class="text-sm font-medium text-gray-800">{{ type.name }}</span>
                                <span v-if="!type.is_active" class="ml-2 text-xs text-gray-400">(inactif)</span>
                                <div class="text-xs text-gray-400 mt-0.5 flex gap-3 flex-wrap">
                                    <span v-if="type.interval_km">tous les {{ type.interval_km.toLocaleString('fr-FR') }} km</span>
                                    <span v-if="type.interval_days">tous les {{ type.interval_days }} j</span>
                                    <span v-if="type.alert_threshold_km">alerte à {{ type.alert_threshold_km.toLocaleString('fr-FR') }} km</span>
                                    <span v-if="type.alert_threshold_days">alerte à {{ type.alert_threshold_days }} j</span>
                                </div>
                            </div>
                            <div class="flex gap-1 ml-2 shrink-0">
                                <button type="button" @click="startEdit(type)" class="text-gray-400 hover:text-orange-500 p-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button type="button" @click="deleteType(type.id)" class="text-gray-400 hover:text-red-500 p-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Formulaire d'édition inline -->
                        <div v-else class="border border-orange-200 rounded-lg p-3 space-y-2 bg-orange-50">
                            <input v-model="editForm.name" type="text" placeholder="Nom *"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 bg-white"
                                :class="{ 'border-red-400': editForm.errors.name }" />
                            <div class="grid grid-cols-2 gap-2">
                                <input v-model="editForm.interval_km" type="number" min="1" placeholder="Intervalle km"
                                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 bg-white" />
                                <input v-model="editForm.interval_days" type="number" min="1" placeholder="Intervalle jours"
                                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 bg-white" />
                                <input v-model="editForm.alert_threshold_km" type="number" min="0" placeholder="Seuil alerte km"
                                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 bg-white" />
                                <input v-model="editForm.alert_threshold_days" type="number" min="0" placeholder="Seuil alerte jours"
                                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 bg-white" />
                            </div>
                            <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                <input v-model="editForm.is_active" type="checkbox" class="accent-orange-500" />
                                Actif
                            </label>
                            <div class="flex gap-2">
                                <button type="button" @click="submitEdit(type.id)" :disabled="editForm.processing"
                                    class="flex-1 bg-orange-500 hover:bg-orange-600 disabled:opacity-50 text-white text-sm font-medium py-2 rounded-lg transition-colors">
                                    Enregistrer
                                </button>
                                <button type="button" @click="cancelEdit"
                                    class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium py-2 rounded-lg transition-colors">
                                    Annuler
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulaire d'ajout -->
                <div v-if="showAddForm" class="border border-orange-200 rounded-lg p-3 space-y-2 bg-orange-50 mb-3">
                    <input v-model="addForm.name" type="text" placeholder="Nom *"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 bg-white"
                        :class="{ 'border-red-400': addForm.errors.name }" />
                    <p v-if="addForm.errors.name" class="text-xs text-red-500">{{ addForm.errors.name }}</p>
                    <div class="grid grid-cols-2 gap-2">
                        <input v-model="addForm.interval_km" type="number" min="1" placeholder="Intervalle km"
                            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 bg-white" />
                        <input v-model="addForm.interval_days" type="number" min="1" placeholder="Intervalle jours"
                            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 bg-white" />
                        <input v-model="addForm.alert_threshold_km" type="number" min="0" placeholder="Seuil alerte km"
                            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 bg-white" />
                        <input v-model="addForm.alert_threshold_days" type="number" min="0" placeholder="Seuil alerte jours"
                            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 bg-white" />
                    </div>
                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                        <input v-model="addForm.is_active" type="checkbox" class="accent-orange-500" />
                        Actif
                    </label>
                    <div class="flex gap-2">
                        <button type="button" @click="submitAdd" :disabled="addForm.processing"
                            class="flex-1 bg-orange-500 hover:bg-orange-600 disabled:opacity-50 text-white text-sm font-medium py-2 rounded-lg transition-colors">
                            Ajouter
                        </button>
                        <button type="button" @click="showAddForm = false"
                            class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium py-2 rounded-lg transition-colors">
                            Annuler
                        </button>
                    </div>
                </div>

                <button v-if="!showAddForm" type="button" @click="showAddForm = true"
                    class="text-sm text-orange-500 hover:text-orange-700 font-medium flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Ajouter un type
                </button>
            </div>

            <!-- Section API -->
            <div class="bg-white rounded-xl shadow p-5">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3">API Claude (IA)</h2>
                <div class="flex items-center gap-3 bg-gray-50 rounded-lg px-3 py-2.5">
                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    <span class="text-sm font-mono text-gray-600">{{ maskedApiKey ?? 'Non configurée' }}</span>
                </div>
                <p class="text-xs text-gray-400 mt-2">Configurée via la variable d'environnement <code class="bg-gray-100 px-1 rounded">ANTHROPIC_API_KEY</code>.</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    motorcycle: Object,
    maintenanceTypes: Array,
    maskedApiKey: String,
})

// --- Moto ---
const motoForm = useForm({
    make:            props.motorcycle.make,
    model:           props.motorcycle.model,
    year:            props.motorcycle.year,
    plate:           props.motorcycle.plate ?? '',
    initial_mileage: props.motorcycle.initial_mileage,
    photo:           null,
})

function submitMotorcycle() {
    motoForm.put(route('settings.motorcycle'), {
        forceFormData: true,
    })
}

// --- Discord ---
const discordForm = useForm({
    discord_webhook_url: props.motorcycle.discord_webhook_url ?? '',
})

function submitDiscord() {
    discordForm.put(route('settings.discord'))
}

const testForm = useForm({})
function testDiscord() {
    testForm.post(route('settings.discord.test'))
}

// --- Types d'entretien ---
const showAddForm = ref(false)
const editingId   = ref(null)

const addForm = useForm({
    name:                  '',
    interval_km:           null,
    interval_days:         null,
    alert_threshold_km:    null,
    alert_threshold_days:  null,
    is_active:             true,
})

function submitAdd() {
    addForm.post(route('maintenance-types.store'), {
        onSuccess: () => {
            addForm.reset()
            showAddForm.value = false
        },
    })
}

const editForm = useForm({
    name:                  '',
    interval_km:           null,
    interval_days:         null,
    alert_threshold_km:    null,
    alert_threshold_days:  null,
    is_active:             true,
})

function startEdit(type) {
    editingId.value = type.id
    editForm.name                 = type.name
    editForm.interval_km          = type.interval_km ?? null
    editForm.interval_days        = type.interval_days ?? null
    editForm.alert_threshold_km   = type.alert_threshold_km ?? null
    editForm.alert_threshold_days = type.alert_threshold_days ?? null
    editForm.is_active            = type.is_active
}

function cancelEdit() {
    editingId.value = null
    editForm.reset()
}

function submitEdit(id) {
    editForm.put(route('maintenance-types.update', id), {
        onSuccess: () => {
            editingId.value = null
            editForm.reset()
        },
    })
}

const deleteForm = useForm({})
function deleteType(id) {
    deleteForm.delete(route('maintenance-types.destroy', id))
}
</script>
