<template>
  <div class="flex min-h-screen">
    <!-- Sidebar -->
    <SuperAdminSidebar 
      ref="sidebarRef"
      :sidebar-open="sidebarOpen"
      @update:sidebarOpen="sidebarOpen = $event"
      @toggle-collapse="isSidebarCollapsed = $event"
    />

    <!-- Main Content -->
    <div 
      class="flex-1 flex flex-col transition-all duration-300"
      :class="isSidebarCollapsed ? 'md:ml-20' : 'md:ml-64'"
    >
      <!-- Header -->
      <SuperAdminHeader 
        :title="pageTitle || 'Dashboard'"
        :mobile-title="mobilePageTitle || 'Dashboard'"
        :is-collapsed="isSidebarCollapsed"
        @toggle-sidebar="toggleSidebar"
      >
        <template #header>
          <slot name="header" />
        </template>
      </SuperAdminHeader>

      <!-- Page Content -->
      <main class="flex-1 bg-gray-50 transition-all duration-300">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import SuperAdminSidebar from '@/Components/SuperAdminSidebar.vue';
import SuperAdminHeader from '@/Components/SuperAdminHeader.vue';

// Props for page title
const props = defineProps({
  pageTitle: {
    type: String,
    default: ''
  },
  mobilePageTitle: {
    type: String,
    default: ''
  }
});

// State
const sidebarOpen = ref(true);
const isSidebarCollapsed = ref(false);
const sidebarRef = ref(null);

// Methods
const toggleSidebar = () => {
  if (window.innerWidth >= 768) {
    if (sidebarRef.value) {
      sidebarRef.value.toggleCollapse();
    }
  } else {
    sidebarOpen.value = !sidebarOpen.value;
  }
};
</script>