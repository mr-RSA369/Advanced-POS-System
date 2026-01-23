
<template>
    <div class="fixed top-4 right-4 z-[10000] w-96 max-w-full">
        <transition-group name="alert">
            <div
                v-for="alert in alerts"
                :key="alert.id"
                :class="[
                    'mb-2 rounded-lg p-4 shadow-lg border',
                    alert.type === 'success'
                        ? 'bg-green-50 border-green-200 text-green-800'
                        : alert.type === 'error'
                        ? 'bg-red-50 border-red-200 text-red-800'
                        : 'bg-blue-50 border-blue-200 text-blue-800'
                ]"
            >
                <div class="flex items-start">
                    <!-- Icon -->
                    <div :class="[
                        'flex-shrink-0',
                        alert.type === 'success' ? 'text-green-400'
                        : alert.type === 'error' ? 'text-red-400'
                        : 'text-blue-400'
                    ]">
                        <svg v-if="alert.type === 'success'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <svg v-else-if="alert.type === 'error'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                    </div>

                    <!-- Message -->
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium">{{ alert.message }}</p>
                    </div>

                    <!-- Close button -->
                    <button
                        @click="removeAlert(alert.id)"
                        :class="[
                            'ml-4 flex-shrink-0 rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none',
                            alert.type === 'success' ? 'hover:bg-green-100'
                            : alert.type === 'error' ? 'hover:bg-red-100'
                            : 'hover:bg-blue-100'
                        ]"
                    >
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                        </svg>
                    </button>
                </div>
            </div>
        </transition-group>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const alerts = ref([])

const showAlert = (type, message, duration = 5000) => {
    const id = Date.now() + Math.random()

    alerts.value.push({
        id,
        type,
        message
    })

    // Auto remove after duration
    if (duration > 0) {
        setTimeout(() => {
            removeAlert(id)
        }, duration)
    }
}

const removeAlert = (id) => {
    alerts.value = alerts.value.filter(alert => alert.id !== id)
}

// Handle Inertia.js navigation events
const clearAlertsOnNavigation = () => {
    alerts.value = []
}

// Make function available globally
onMounted(() => {
    window.showCustomAlert = showAlert
    // Clear alerts on Inertia navigation
    window.addEventListener('popstate', clearAlertsOnNavigation)
})

onUnmounted(() => {
    window.showCustomAlert = null
    window.removeEventListener('popstate', clearAlertsOnNavigation)
})

defineExpose({ showAlert })
</script>

<style scoped>
.alert-enter-active,
.alert-leave-active {
    transition: all 0.3s ease;
}

.alert-enter-from,
.alert-leave-to {
    opacity: 0;
    transform: translateX(100px);
}

.alert-leave-active {
    position: absolute;
}
</style>
