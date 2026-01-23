<template>
  <Link
    :href="route(name)"
    class="block px-4 py-2 rounded-md transition hover:bg-gray-800"
    :class="$page.url === route(name) ? 'bg-gray-800 text-white' : ''"
  >
    {{ label }}
  </Link>
</template>

<script setup>
defineProps({
  name: String,
  label: String
})
</script>
