<template>
    <div class="min-h-screen bg-gray-50 pb-8">
        <!-- Header -->
        <div class="bg-white shadow-sm px-4 py-4 flex items-center gap-3">
            <a :href="route('maintenance.show', maintenance.id)" class="text-gray-400 hover:text-gray-600 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h1 class="text-lg font-bold text-gray-900">Modifier l'intervention</h1>
        </div>

        <form @submit.prevent="submit" class="px-4 mt-6 space-y-4 max-w-2xl mx-auto">
            <!-- Infos principales -->
            <div class="bg-white rounded-xl shadow p-5 space-y-4">
                <div>
                    <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Date *</label>
                    <input v-model="form.performed_at" type="date"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
                        :class="{ 'border-red-400': form.errors.performed_at }" />
                    <p v-if="form.errors.performed_at" class="text-xs text-red-500 mt-1">{{ form.errors.performed_at }}</p>
                </div>

                <div>
                    <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Kilométrage *</label>
                    <input v-model="form.mileage" type="number" min="0"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
                        :class="{ 'border-red-400': form.errors.mileage }" />
                    <p v-if="form.errors.mileage" class="text-xs text-red-500 mt-1">{{ form.errors.mileage }}</p>
                </div>

                <div>
                    <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Garage</label>
                    <input v-model="form.garage" type="text"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400" />
                </div>

                <div>
                    <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Notes</label>
                    <textarea v-model="form.notes" rows="2"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 resize-none"></textarea>
                </div>
            </div>

            <!-- Items -->
            <div class="bg-white rounded-xl shadow p-5">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-3">Prestations *</p>
                <p v-if="form.errors.items" class="text-xs text-red-500 mb-2">{{ form.errors.items }}</p>

                <div v-for="(item, index) in form.items" :key="index" class="flex gap-2 mb-2">
                    <input v-model="item.label" type="text" placeholder="ex: Vidange huile"
                        class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
                        :class="{ 'border-red-400': form.errors[`items.${index}.label`] }" />
                    <input v-model="item.amount" type="number" min="0" step="0.01" placeholder="€"
                        class="w-24 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400" />
                    <button v-if="form.items.length > 1" type="button" @click="removeItem(index)"
                        class="text-gray-300 hover:text-red-400 p-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <button type="button" @click="addItem"
                    class="mt-2 text-sm text-orange-500 hover:text-orange-700 font-medium flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Ajouter une prestation
                </button>
            </div>

            <!-- Proposition mise à jour kilométrage -->
            <div v-if="showMileageProposal" class="bg-orange-50 border border-orange-200 rounded-xl p-4 flex items-start gap-3">
                <input id="update_mileage" v-model="form.update_mileage_log" type="checkbox"
                    class="mt-0.5 accent-orange-500" />
                <label for="update_mileage" class="text-sm text-orange-800 cursor-pointer">
                    Le kilométrage saisi ({{ Number(form.mileage).toLocaleString('fr-FR') }} km) est supérieur au kilométrage actuel
                    ({{ currentMileage.toLocaleString('fr-FR') }} km). Mettre à jour le kilométrage de la moto ?
                </label>
            </div>

            <button type="submit" :disabled="form.processing"
                class="w-full bg-orange-500 hover:bg-orange-600 disabled:opacity-50 text-white font-medium py-3 px-4 rounded-xl text-sm transition-colors">
                Enregistrer les modifications
            </button>
        </form>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { useMileageCheck } from '@/composables/useMileageCheck'

const props = defineProps({
    motorcycle: Object,
    maintenance: Object,
    currentMileage: Number,
})

const form = useForm({
    performed_at: props.maintenance.performed_at
        ? props.maintenance.performed_at.substring(0, 10)
        : '',
    mileage: props.maintenance.mileage,
    garage:  props.maintenance.garage ?? '',
    notes:   props.maintenance.notes ?? '',
    items: props.maintenance.maintenance_items.map(item => ({
        label:  item.label,
        amount: item.amount ?? '',
    })),
    update_mileage_log: false,
})

const { showMileageProposal } = useMileageCheck(form, props.currentMileage)

function addItem() {
    form.items.push({ label: '', amount: '' })
}

function removeItem(index) {
    form.items.splice(index, 1)
}

function submit() {
    form.put(route('maintenance.update', props.maintenance.id))
}
</script>
