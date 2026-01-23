<script setup>
import { ref, computed } from 'vue'

const model = defineModel({
    type: null,
    required: true,
})

const props = defineProps({
    name: {
        type: String,
        required: true,
    },
    type: {
        type: String,
        default: "text",
    },
    placeholder: String,
    errMessage: String,
    icon: String, // SVG path for icon
    greenTheme: {
        type: Boolean,
        default: true
    },
    disabled: {
        type: Boolean,
        default: false
    }
})

const isPassword = computed(() => props.type === 'password')
const showPassword = ref(false)
const inputType = computed(() => {
    if (!isPassword.value) return props.type
    return showPassword.value ? 'text' : 'password'
})

const togglePassword = () => {
    if (isPassword.value) {
        showPassword.value = !showPassword.value
    }
}

// Common SVG icons
const iconPaths = {
    user: "M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z",
    email: "M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z",
    lock: "M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z",
    check: "M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z",
    phone: "M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z",
    search: "M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z",
    calendar: "M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z",
    dollar: "M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
}

const getIconPath = () => {
    if (props.icon && iconPaths[props.icon]) {
        return iconPaths[props.icon]
    }
    return props.icon
}

const hasIcon = computed(() => Boolean(props.icon || (isPassword.value && props.greenTheme)))
</script>

<template>
    <div class="mb-6">
        <label :for="name" class="block text-sm font-medium mb-2 transition-colors duration-200"
               :class="[
                   props.greenTheme ? 'text-gray-700 hover:text-green-700' : 'text-gray-700',
                   props.errMessage ? 'text-red-600' : ''
               ]">
            {{ name }}
            <span v-if="errMessage" class="text-red-500 ml-1">*</span>
        </label>

        <div class="relative">
            <!-- Left Icon -->
            <div v-if="hasIcon" class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg v-if="!isPassword.value && getIconPath()" class="w-5 h-5 transition-colors duration-200"
                     :class="[
                         props.greenTheme ? 'text-green-500 group-focus-within:text-green-600' : 'text-gray-400',
                         props.errMessage ? 'text-red-500' : ''
                     ]"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="getIconPath()" />
                </svg>
                <svg v-else-if="isPassword.value" class="w-5 h-5 text-gray-400 group-focus-within:text-green-500"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>

            <!-- Input Field -->
            <input
                :id="name"
                :type="inputType"
                :placeholder="placeholder"
                v-model="model"
                :disabled="disabled"
                class="w-full transition-all duration-300 ease-in-out group"
                :class="[
                    hasIcon ? 'pl-10' : 'pl-4',
                    isPassword.value ? 'pr-10' : 'pr-4',
                    'py-2 rounded-xl border focus:outline-none focus:ring-2',
                    'disabled:opacity-50 disabled:cursor-not-allowed',
                    props.greenTheme
                        ? 'border-gray-300 bg-white hover:border-green-400 focus:border-green-500 focus:ring-green-200'
                        : 'border-gray-300 bg-white hover:border-blue-400 focus:border-blue-500 focus:ring-blue-200',
                    props.errMessage
                        ? 'border-red-300 hover:border-red-400 focus:border-red-500 focus:ring-red-200 text-red-900'
                        : ''
                ]"
                @focus="!errMessage && greenTheme ? 'border-green-500' : ''"
            />

            <!-- Password Toggle Button -->
            <button
                v-if="isPassword.value && !disabled"
                type="button"
                @click="togglePassword"
                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                :class="props.errMessage ? 'text-red-400 hover:text-red-600' : 'text-gray-400 hover:text-green-600'"
            >
                <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 015 12c-1.274 4.057-5.064 7-9.543 7 4.478 0 8.268-2.943 9.543-7 0-1.055-.163-2.07-.461-3.028M12 12l.01.01" />
                </svg>
            </button>

            <!-- Success/Error Indicator -->
            <div v-if="!disabled" class="absolute inset-y-0 right-0 flex items-center pr-3">
                <svg v-if="errMessage" class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg v-else-if="model && !errMessage && greenTheme" class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </div>

        <!-- Error Message -->
        <transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="transform -translate-y-2 opacity-0"
            enter-to-class="transform translate-y-0 opacity-100"
            leave-active-class="transition-all duration-300 ease-in"
            leave-from-class="transform translate-y-0 opacity-100"
            leave-to-class="transform -translate-y-2 opacity-0"
        >
            <small v-if="errMessage" class="text-red-500 text-sm mt-2 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ errMessage }}
            </small>
        </transition>

        <!-- Character Counter (for text inputs) -->
        <div v-if="type === 'text' && model && greenTheme" class="text-right mt-1">
            <span class="text-xs" :class="model.length > 50 ? 'text-green-600' : 'text-gray-400'">
                {{ model.length }}/100
            </span>
        </div>
    </div>
</template>

<style scoped>
/* Custom focus styles */
input:focus {
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

/* Smooth transitions */
input {
    transition-property: border-color, box-shadow, background-color;
}

/* Password strength indicator */
.password-strength {
    height: 3px;
    margin-top: 2px;
    border-radius: 1px;
    transition: width 0.3s ease;
}

/* Hover effects */
input:not(:disabled):hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

/* Animation for success check */
@keyframes checkAppear {
    0% { transform: scale(0); opacity: 0; }
    70% { transform: scale(1.2); opacity: 1; }
    100% { transform: scale(1); opacity: 1; }
}

svg.text-green-500 {
    animation: checkAppear 0.3s ease-out;
}
</style>
