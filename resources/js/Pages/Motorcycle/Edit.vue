<template>
    <div class="min-h-screen bg-gray-50 px-4 py-8">
        <div class="max-w-lg mx-auto">
            <div class="mb-8 flex items-center gap-3">
                <a :href="route('dashboard')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Modifier ma moto</h1>
                    <p class="text-sm text-gray-500 mt-0.5">{{ motorcycle.make }} {{ motorcycle.model }} {{ motorcycle.year }}</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="bg-white shadow rounded-xl p-6 space-y-5">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Marque <span class="text-red-500">*</span></label>
                        <input v-model="form.make" type="text" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                            :class="{ 'border-red-500': form.errors.make }" />
                        <p v-if="form.errors.make" class="mt-1 text-xs text-red-600">{{ form.errors.make }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Modèle <span class="text-red-500">*</span></label>
                        <input v-model="form.model" type="text" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                            :class="{ 'border-red-500': form.errors.model }" />
                        <p v-if="form.errors.model" class="mt-1 text-xs text-red-600">{{ form.errors.model }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Année <span class="text-red-500">*</span></label>
                        <input v-model="form.year" type="number" required min="1900" :max="new Date().getFullYear() + 1"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                            :class="{ 'border-red-500': form.errors.year }" />
                        <p v-if="form.errors.year" class="mt-1 text-xs text-red-600">{{ form.errors.year }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Immatriculation</label>
                        <input v-model="form.plate" type="text"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                            :class="{ 'border-red-500': form.errors.plate }" />
                        <p v-if="form.errors.plate" class="mt-1 text-xs text-red-600">{{ form.errors.plate }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kilométrage initial <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input v-model="form.initial_mileage" type="number" required min="0"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                            :class="{ 'border-red-500': form.errors.initial_mileage }" />
                        <span class="absolute right-3 top-2 text-sm text-gray-400">km</span>
                    </div>
                    <p v-if="form.errors.initial_mileage" class="mt-1 text-xs text-red-600">{{ form.errors.initial_mileage }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Photo de la moto</label>
                    <img v-if="photoPreview || motorcycle.photo_path" :src="photoPreview || '/storage/' + motorcycle.photo_path"
                        class="mb-3 h-32 w-auto rounded-lg object-cover" />
                    <input type="file" accept="image/jpeg,image/png" @change="onPhotoChange"
                        class="w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100" />
                    <p class="mt-1 text-xs text-gray-400">JPG ou PNG, max 5 Mo. Laissez vide pour conserver la photo actuelle.</p>
                    <p v-if="form.errors.photo" class="mt-1 text-xs text-red-600">{{ form.errors.photo }}</p>
                </div>

                <button type="submit" :disabled="form.processing"
                    class="w-full bg-orange-500 hover:bg-orange-600 disabled:opacity-50 text-white font-medium py-2 px-4 rounded-lg text-sm transition-colors">
                    {{ form.processing ? 'Enregistrement…' : 'Enregistrer les modifications' }}
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    motorcycle: Object,
});

const photoPreview = ref(null);

const form = useForm({
    make:            props.motorcycle.make,
    model:           props.motorcycle.model,
    year:            props.motorcycle.year,
    plate:           props.motorcycle.plate ?? '',
    initial_mileage: props.motorcycle.initial_mileage,
    photo:           null,
    _method:         'PUT',
});

function onPhotoChange(e) {
    const file = e.target.files[0];
    if (!file) return;
    form.photo = file;
    photoPreview.value = URL.createObjectURL(file);
}

function submit() {
    form.post(`/motorcycle/${props.motorcycle.id}`);
}
</script>
