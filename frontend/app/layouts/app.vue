<script setup>
</script>

<template>
  <div class="flex min-h-screen bg-slate-50">
    <AppSidebar />

    <div class="flex-1 flex flex-col min-w-0">
      <main class="flex-1 p-4 lg:p-8">
        <slot />
      </main>
    </div>
  </div>
</template>
