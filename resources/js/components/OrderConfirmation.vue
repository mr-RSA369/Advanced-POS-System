<template>
    <div v-if="show" class="fixed inset-0 z-[9999] overflow-y-auto">
        <div
            class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm transition-opacity duration-300"
            :class="show ? 'opacity-100' : 'opacity-0'"
            @click="$emit('close')"
        ></div>

        <!-- Modal container-->
        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all duration-300 sm:my-8 sm:w-full sm:max-w-md"
                :class="show ? 'opacity-100 translate-y-0 scale-100' : 'opacity-0 translate-y-4 scale-95'"
                @click.stop
            >
                <!-- Modal content -->
                <div class="bg-white px-6 pb-6 pt-7 sm:p-8">
                    <div class="flex items-start">
                        <!-- Icon -->
                        <div class="flex-shrink-0">
                            <div
                                class="mx-auto flex h-14 w-14 items-center justify-center rounded-full shadow-lg"
                                :class="iconClass"
                            >
                                <svg class="h-7 w-7" :class="iconColor" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path v-if="type === 'close'" stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    <path v-if="type === 'cancel'" stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                    <path v-if="type === 'info'" stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="ml-5 flex-1">
                            <h3 class="text-xl font-bold leading-7 text-gray-900" id="modal-title">
                                {{ title }}
                            </h3>
                            <div class="mt-3">
                                <p class="text-base text-gray-600">
                                    {{ message }}
                                </p>
                                <div v-if="orderId" class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Order ID: <span class="font-semibold">{{ orderDisplayId }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal buttons -->
                <div class="bg-gray-50 px-6 py-5 sm:flex sm:flex-row-reverse sm:gap-3 sm:px-8 rounded-b-2xl">
                    <button
                        type="button"
                        @click="$emit('confirm')"
                        :disabled="loading"
                        class="inline-flex w-full justify-center items-center rounded-xl px-5 py-3.5 text-base font-semibold text-white shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 sm:w-auto sm:min-w-[120px]"
                        :class="confirmButtonClass"
                    >
                        <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span v-if="!loading">{{ confirmText }}</span>
                        <span v-else>{{ loadingText }}</span>
                    </button>

                    <button
                        type="button"
                        @click="$emit('cancel')"
                        :disabled="loading"
                        class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-3.5 text-base font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 sm:mt-0 sm:w-auto sm:min-w-[120px]"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    show: {
        type: Boolean,
        required: true
    },
    type: {
        type: String,
        required: true,
        validator: (value) => ['close', 'cancel', 'info'].includes(value)
    },
    title: {
        type: String,
        required: true
    },
    message: {
        type: String,
        required: true
    },
    orderId: {
        type: String,
        default: ''
    },
    orderDisplayId: {
        type: String,
        default: ''
    },
    confirmText: {
        type: String,
        default: 'Confirm'
    },
    loadingText: {
        type: String,
        default: 'Processing...'
    },
    loading: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['close', 'confirm', 'cancel'])

// Computed properties for dynamic styling
const iconClass = computed(() => {
    switch (props.type) {
        case 'close': return 'bg-green-100';
        case 'cancel': return 'bg-red-100';
        case 'info': return 'bg-blue-100';
        default: return 'bg-gray-100';
    }
})

const iconColor = computed(() => {
    switch (props.type) {
        case 'close': return 'text-green-600';
        case 'cancel': return 'text-red-600';
        case 'info': return 'text-blue-600';
        default: return 'text-gray-600';
    }
})

const confirmButtonClass = computed(() => {
    switch (props.type) {
        case 'close': return 'bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-green-500';
        case 'cancel': return 'bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 focus:ring-red-500';
        case 'info': return 'bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:ring-blue-500';
        default: return 'bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 focus:ring-gray-500';
    }
})
</script>

<style scoped>
/* Custom backdrop blur effect */
.bg-gray-900\/70 {
    background-color: rgba(17, 24, 39, 0.7);
}

.backdrop-blur-sm {
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}

/* Smooth transitions */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 300ms;
}

/* Prevent background scrolling when modal is open */
.fixed {
    overflow: hidden;
}

/* Ensure modal is on top of everything */
.z-\[9999\] {
    z-index: 9999;
}
</style>
