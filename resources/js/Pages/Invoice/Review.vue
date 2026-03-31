<template>
    <div class="min-h-screen bg-gray-50 pb-8">
        <div class="bg-white shadow-sm px-4 py-4 flex items-center gap-3">
            <h1 class="text-lg font-bold text-gray-900">Vérification de la facture</h1>
        </div>
        <div class="px-4 mt-6 max-w-2xl mx-auto">
            <p class="text-sm text-gray-500">Formulaire de correction à venir (SCRUM-12).</p>
            <pre class="mt-4 bg-white rounded-xl shadow p-4 text-xs overflow-auto">{{ JSON.stringify(invoice.extracted_data, null, 2) }}</pre>
        </div>
    </div>
</template>

<script setup>
defineProps({ invoice: Object })
</script>
