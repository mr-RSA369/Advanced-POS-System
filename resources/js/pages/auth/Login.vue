<script setup>
import { useForm } from "@inertiajs/vue3";
import textinput from "../../components/textinput.vue";

defineOptions({
    layout: null,
});

const form = useForm({
    email: null,
    password: null,
    remember: false,
});

const submit = () => {
    form.post(route("login"), {
        onError: () => form.reset("password"),
    });
};
</script>

<template>
    <Head title="Login" />
    <div class="min-h-screen bg-gradient-to-br from-green-50 to-emerald-100 flex items-center justify-center p-4">
        <div class="absolute top-0 left-0 w-48 h-48 bg-green-200 rounded-full mix-blend-multiply filter blur-3xl opacity-40"></div>
        <div class="absolute bottom-0 right-0 w-48 h-48 bg-emerald-300 rounded-full mix-blend-multiply filter blur-3xl opacity-40"></div>
        <div class="relative w-full max-w-xs bg-white/95 rounded-xl shadow-lg overflow-hidden border border-green-100">
            <div class="bg-gradient-to-r from-green-600 to-emerald-600 p-4 text-white">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center mr-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold">Welcome Back</h1>
                        <p class="text-green-100 opacity-90 text-xs">Sign in to continue</p>
                    </div>
                </div>
            </div>

            <!-- Compact Form -->
            <form @submit.prevent="submit" class="p-4 space-y-4">
                <!-- Form Fields -->
                <textinput
                    name="Email"
                    placeholder="Enter your email"
                    v-model="form.email"
                    :errMessage="form.errors.email"
                    icon="email"
                    :greenTheme="true"
                />

                <textinput
                    name="Password"
                    type="password"
                    placeholder="Enter your password"
                    v-model="form.password"
                    :errMessage="form.errors.password"
                    icon="lock"
                    :greenTheme="true"
                />

                <!-- Login Button -->
                <div class="pt-1">
                    <button
                        :disabled="form.processing"
                        class="w-full bg-gradient-to-r from-green-600 to-emerald-600 text-white text-sm font-medium py-2.5 px-4 rounded-lg hover:from-green-700 hover:to-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-150 shadow-sm flex items-center justify-center"
                    >
                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span v-if="!form.processing" class="text-sm">Sign In</span>
                        <span v-else class="text-sm">Signing In...</span>
                    </button>
                </div>
            </form>

            <div class="h-1 bg-gradient-to-r from-green-600 to-emerald-600"></div>
        </div>
    </div>
</template>

<style>

input:focus {
    outline: none;
    ring: 2px;
    ring-color: #10b981;
}

</style>
