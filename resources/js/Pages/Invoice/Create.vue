<template>
    <div class="min-h-screen bg-gray-50 pb-8">
        <!-- Header -->
        <div class="bg-white shadow-sm px-4 py-4 flex items-center gap-3">
            <a :href="route('dashboard')" class="text-gray-400 hover:text-gray-600 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h1 class="text-lg font-bold text-gray-900">Importer une facture</h1>
        </div>

        <form @submit.prevent="submit" class="px-4 mt-6 space-y-4 max-w-2xl mx-auto">
            <!-- Zone upload -->
            <div class="bg-white rounded-xl shadow p-5">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-3">Photo de la facture</p>

                <!-- Preview -->
                <div v-if="preview" class="relative mb-4">
                    <img :src="preview" alt="Aperçu de la facture"
                        class="w-full rounded-lg object-contain max-h-80 bg-gray-100" />
                    <button type="button" @click="clearPhoto"
                        class="absolute top-2 right-2 bg-white rounded-full p-1.5 shadow text-gray-500 hover:text-red-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Zone de dépôt -->
                <label v-if="!preview" for="photo-input"
                    class="flex flex-col items-center justify-center border-2 border-dashed rounded-xl p-8 cursor-pointer transition-colors"
                    :class="isDragging
                        ? 'border-orange-400 bg-orange-50'
                        : form.errors.photo
                            ? 'border-red-400 bg-red-50'
                            : 'border-gray-200 hover:border-orange-300 hover:bg-orange-50'"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="onDrop">
                    <svg class="w-10 h-10 mb-3 transition-colors" :class="isDragging ? 'text-orange-400' : 'text-gray-300'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-sm font-medium text-gray-600 mb-1">
                        {{ isDragging ? 'Déposer la photo ici' : 'Glisser-déposer ou cliquer pour choisir' }}
                    </span>
                    <span class="text-xs text-gray-400">JPG ou PNG · compressé automatiquement</span>
                </label>

                <!-- Input unique toujours présent dans le DOM -->
                <input id="photo-input" ref="fileInput" type="file" accept="image/jpeg,image/png" capture="environment"
                    class="hidden" @change="onFileChange" />

                <p v-if="form.errors.photo" class="text-xs text-red-500 mt-2">{{ form.errors.photo }}</p>

                <!-- Bouton changer la photo -->
                <button v-if="preview" type="button" @click="triggerFileInput"
                    class="mt-3 w-full text-sm text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg py-2 transition-colors">
                    Changer la photo
                </button>
            </div>

            <!-- Info extraction IA -->
            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 flex items-start gap-3">
                <svg class="w-4 h-4 text-blue-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-xs text-blue-700">
                    Les informations de la facture seront extraites automatiquement par IA.
                    Tu pourras les vérifier et les corriger avant d'enregistrer l'intervention.
                </p>
            </div>

            <!-- Submit -->
            <button type="submit" :disabled="!preview || form.processing"
                class="w-full bg-orange-500 hover:bg-orange-600 disabled:opacity-50 text-white font-medium py-3 px-4 rounded-xl text-sm transition-colors">
                <span v-if="form.processing">Envoi en cours…</span>
                <span v-else>Analyser la facture</span>
            </button>
        </form>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const preview = ref(null)
const fileInput = ref(null)
const isDragging = ref(false)

const form = useForm({
    photo: null,
})

function loadFile(file) {
    if (!file) return

    form.photo = file

    const reader = new FileReader()
    reader.onload = (e) => { preview.value = e.target.result }
    reader.readAsDataURL(file)
}

function onFileChange(event) {
    loadFile(event.target.files[0])
}

function onDrop(event) {
    isDragging.value = false
    loadFile(event.dataTransfer.files[0])
}

function clearPhoto() {
    preview.value = null
    form.photo = null
    if (fileInput.value) fileInput.value.value = ''
}

function triggerFileInput() {
    fileInput.value?.click()
}

function submit() {
    form.post(route('invoice.store'))
}
</script>
