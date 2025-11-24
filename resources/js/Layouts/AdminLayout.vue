<template>
  <div class="flex h-screen bg-gray-50">
    <!-- Mobile sidebar overlay -->
    <div 
      v-if="sidebarOpen && !isSidebarCollapsed" 
      class="fixed inset-0 bg-black/30 z-40 md:hidden transition-opacity duration-300"
      @click="sidebarOpen = false"
    ></div>

    <!-- Sidebar -->
    <AdminSidebar 
      ref="sidebarRef"
      :sidebarOpen="sidebarOpen" 
      @update:sidebarOpen="sidebarOpen = $event"
      @toggle-collapse="handleSidebarToggle"
    />

    <!-- Main Content Wrapper -->
    <div 
      class="flex-1 flex flex-col overflow-hidden transition-all duration-300 ease-in-out"
      :class="sidebarOpen ? (isSidebarCollapsed ? 'md:ml-20' : 'md:ml-64') : 'ml-0'"
    >
      <!-- Header -->
      <AdminHeader 
        :title="pageTitle || 'Dashboard'"
        :mobile-title="mobilePageTitle || 'Dashboard'"
        role="Admin"
        :user-name="$page.props.auth.user.name"
        :user-email="$page.props.auth.user.email"
        :user-profile-pic="$page.props.auth.user.profile_pict"
        @toggle-sidebar="toggleSidebar"
      >
        <template #header>
          <slot name="header" />
        </template>
      </AdminHeader>

      <!-- Page Content -->
      <main class="flex-1 overflow-y-auto bg-gray-50">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import AdminSidebar from '@/Components/AdminSidebar.vue';
import AdminHeader from '@/Components/AdminHeader.vue';
import { MenuIcon } from 'lucide-vue-next';

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

const handleSidebarToggle = (collapsed) => {
  isSidebarCollapsed.value = collapsed;
};

// Close sidebar when clicking on overlay on mobile
const closeSidebarOnOverlayClick = (event) => {
  if (!sidebarOpen.value) return;
  
  const sidebar = document.querySelector('aside');
  if (sidebar && !sidebar.contains(event.target) && window.innerWidth < 768) {
    sidebarOpen.value = false;
  }
};

// Lifecycle
onMounted(() => {
  document.addEventListener('click', closeSidebarOnOverlayClick);
});

onUnmounted(() => {
  document.removeEventListener('click', closeSidebarOnOverlayClick);
});
</script>