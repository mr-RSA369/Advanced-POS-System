<template>
  <!-- Overlay -->
  <div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center">

    <!-- Modal Container -->
    <div
      class="bg-white w-full max-w-2xl rounded-xl shadow-lg
             max-h-[90vh] overflow-hidden relative"
    >

      <!-- Header -->
      <div class="flex justify-between items-center px-6 py-4 border-b">
        <h2 class="text-lg font-semibold">Add Category</h2>
        <button
          class="text-gray-500 hover:text-black"
          @click="$emit('close')"
        >
          ✕
        </button>
      </div>

      <!-- Body -->
      <div class="px-6 py-4 overflow-y-auto max-h-[calc(90vh-64px)]">
        <form @submit.prevent="submitForm" class="space-y-5">

          <div>
            <label class="block mb-1 text-sm font-medium">Category Name</label>
            <input
              v-model="form.name"
              type="text"
              :class="[
                'w-full border-2 rounded-lg px-3 py-2 focus:outline-none',
                errors?.name ? 'border-red-500' : 'border-gray-300 focus:border-green-700'
              ]"
            />
            <small v-if="errors.name" class="text-red-600 text-sm">
              {{ errors.name[0] }}
            </small>
          </div>

          <button
            type="submit"
            class="w-full bg-green-700 text-white py-2 rounded-lg"
          >
            Add
          </button>

        </form>
      </div>

    </div>
  </div>
</template>


<script setup>
import { ref } from 'vue'
import axios from 'axios'

const emit = defineEmits(['close'])

const form = ref({
  name: ''
})

const errors = ref({})

const submitForm = async () => {
  errors.value = {}

  try {
    await axios.post('/api/categories', {
      name: form.value.name
    })

    form.value.name = ''
    emit('close')
    window.location.reload()

  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors
    } else {
      console.error('Failed to add category', error)
    }
  }
}
</script>
