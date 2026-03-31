<template>
    <div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center px-4">
        <div class="bg-white rounded-xl shadow p-8 max-w-sm w-full text-center">
            <!-- Spinner -->
            <div class="flex justify-center mb-5">
                <svg class="w-12 h-12 text-orange-400 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
            </div>

            <h1 class="text-lg font-bold text-gray-900 mb-2">Analyse en cours…</h1>
            <p class="text-sm text-gray-500">
                L'IA extrait les informations de ta facture.
                Cette opération prend quelques secondes.
            </p>
        </div>
    </div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    invoice: Object,
})

let interval = null

onMounted(() => {
    // Polling toutes les 2s pour vérifier le statut (implémenté en SCRUM-11)
    interval = setInterval(() => {
        router.reload({ only: ['invoice'], onSuccess: () => {
            if (props.invoice.extraction_status === 'done' || props.invoice.extraction_status === 'failed') {
                clearInterval(interval)
                router.visit(route('invoice.review', props.invoice.id))
            }
        }})
    }, 2000)
})

onUnmounted(() => {
    clearInterval(interval)
})
</script>
