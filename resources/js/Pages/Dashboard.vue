<template>
    <div class="min-h-screen bg-gray-50 p-6">
        <h1 class="text-xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Bienvenue sur votre carnet moto.</p>
        <form method="POST" action="/logout" class="mt-6">
            <input type="hidden" name="_token" :value="$page.props.csrf_token" />
            <button type="submit" class="text-sm text-red-500 hover:underline">Se déconnecter</button>
        </form>
    </div>
</template>
