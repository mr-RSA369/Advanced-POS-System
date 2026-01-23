<template>
    <div v-if="show" class="fixed inset-0 z-[9999] overflow-y-auto">
        <div
            class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm transition-opacity duration-300"
            :class="show ? 'opacity-100' : 'opacity-0'"
            @click="$emit('close')"
        ></div>
        <!-- Modal container -->
        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all duration-300 sm:my-8 sm:w-full sm:max-w-lg"
                :class="show ? 'opacity-100 translate-y-0 scale-100' : 'opacity-0 translate-y-4 scale-95'"
                @click.stop
            >
                <!-- Modal content -->
                <div class="bg-white px-6 pb-6 pt-7 sm:p-8">
                    <div class="flex items-start">
                        <!-- Icon -->
                        <div class="flex-shrink-0">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-blue-100 to-blue-50 shadow-lg">
                                <svg class="h-7 w-7 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="ml-5 flex-1">
                            <h3 class="text-xl font-bold leading-7 text-gray-900" id="modal-title">
                                Print Kitchen Bill?
                            </h3>
                            <div class="mt-3">
                                <p class="text-base text-gray-600">
                                    Would you like to print a kitchen bill for order
                                    <span class="font-bold text-blue-700">{{ orderId }}</span>?
                                </p>
                                <p class="text-sm text-gray-500 mt-2">
                                    This will send the order details to the kitchen printer immediately.
                                </p>
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
                        class="inline-flex w-full justify-center items-center rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 px-5 py-3.5 text-base font-semibold text-white shadow-lg hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 sm:w-auto sm:min-w-[180px]"
                    >
                        <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span v-if="!loading" class="flex items-center">
                            <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
                            </svg>
                            Print Kitchen Bill
                        </span>
                        <span v-else>Printing...</span>
                    </button>

                    <button
                        type="button"
                        @click="$emit('skip')"
                        :disabled="loading"
                        class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-3.5 text-base font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 sm:mt-0 sm:w-auto sm:min-w-[180px]"
                    >
                        Skip Printing
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    orderId: {
        type: String,
        required: true
    },
    show: {
        type: Boolean,
        required: true
    },
    loading: {
        type: Boolean,
        default: false
    }
})

defineEmits(['close', 'confirm', 'skip'])
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
