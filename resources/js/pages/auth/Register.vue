<script setup>
import { useForm } from '@inertiajs/vue3';
import textinput from "../../components/textinput.vue";

defineOptions({
    layout: null,
});

const form = useForm({
    name: null,
    email: null,
    password: null,
    password_confirmation: null,
    avatar: null
})

const change = (e) => {
    form.avatar = e.target.files[0];
    if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
            document.getElementById('avatar-preview').src = e.target.result;
            document.getElementById('avatar-preview').classList.remove('hidden');
        }
        reader.readAsDataURL(e.target.files[0]);
    }
}

const submit = () => {
    form.post(route("register"), {
        onError: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <Head title="Register" />
    <div class="min-h-screen bg-gradient-to-br from-green-50 to-emerald-100 flex items-center justify-center p-4">

        <div class="absolute top-10 left-10 w-72 h-72 bg-green-200 rounded-full mix-blend-multiply filter blur-3xl opacity-70"></div>
        <div class="absolute bottom-10 right-10 w-72 h-72 bg-emerald-300 rounded-full mix-blend-multiply filter blur-3xl opacity-70"></div>

        <!-- Main Card -->
        <div class="relative w-full max-w-md bg-white/95 rounded-xl shadow-lg overflow-hidden border border-green-100">
            <div class="bg-gradient-to-r from-green-600 to-emerald-600 p-5 text-white">
                <div class="flex items-center justify-center mb-2">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold">Create Account</h1>
                        <p class="text-green-100 opacity-90 text-xs">Join our restaurant system</p>
                    </div>
                </div>
            </div>

            <!-- Form Section -->
            <form @submit.prevent="submit" class="p-5 space-y-4">
                <!-- Avatar Upload -->
                <div class="text-center mb-3">
                    <div class="relative inline-block">
                        <div class="w-20 h-20 rounded-full overflow-hidden border-2 border-green-200 bg-green-50 mx-auto">
                            <img id="avatar-preview" class="w-full h-full object-cover hidden" alt="Avatar preview" />
                            <div v-if="!form.avatar" class="w-full h-full flex items-center justify-center text-green-400">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                        </div>
                        <label for="avatar" class="cursor-pointer">
                            <div class="absolute bottom-0 right-0 bg-green-600 text-white p-1.5 rounded-full hover:bg-green-700 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                </svg>
                            </div>
                        </label>
                        <input type="file" id="avatar" @input="change" accept="image/*" class="hidden">
                    </div>
                    <small v-if="form.errors.avatar" class="text-red-500 text-xs block mt-1">{{ form.errors.avatar }}</small>
                </div>

                <!-- Form Fields -->
                <div class="space-y-3">
                    <textinput
                        name="Full Name"
                        placeholder="Enter your full name"
                        v-model="form.name"
                        :errMessage="form.errors.name"
                        icon="user"
                        :greenTheme="true"
                    />

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
                        placeholder="Create a password"
                        v-model="form.password"
                        :errMessage="form.errors.password"
                        icon="lock"
                        :greenTheme="true"
                    />

                    <textinput
                        name="Confirm Password"
                        type="password"
                        placeholder="Re-enter password"
                        v-model="form.password_confirmation"
                        icon="check"
                        :greenTheme="true"
                    />
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button
                        :disabled="form.processing"
                        class="w-full bg-gradient-to-r from-green-600 to-emerald-600 text-white text-sm font-medium py-2.5 px-4 rounded-lg hover:from-green-700 hover:to-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-150 shadow-sm flex items-center justify-center"
                    >
                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span v-if="!form.processing" class="text-sm">Create Account</span>
                        <span v-else class="text-sm">Creating...</span>
                    </button>
                </div>

                <!-- Login Link -->
                <div class="text-center pt-3 border-t border-green-100">
                    <p class="text-gray-600 text-xs">
                        Already have an account?
                        <a :href="route('login')" class="text-green-600 hover:text-green-700 font-medium ml-1">
                            Sign In
                        </a>
                    </p>
                </div>
            </form>

            <!-- Decorative Bottom -->
            <div class="h-1 bg-gradient-to-r from-green-600 to-emerald-600"></div>
        </div>

        <!-- Success Message -->
        <div v-if="form.recentlySuccessful" class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center text-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Account created successfully!
        </div>
    </div>
</template>

<style>

.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
