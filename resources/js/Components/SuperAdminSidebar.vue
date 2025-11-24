<template>
  <aside 
    class="bg-[#111827] text-white flex flex-col fixed h-screen transition-all ease-in-out duration-300 shadow-xl z-50 font-sans md:translate-x-0"
    :class="[
      isCollapsed ? 'w-20' : 'w-64',
      sidebarOpen ? 'translate-x-0' : '-translate-x-full'
    ]"
  >
    <!-- Sidebar Header with Logo and Toggle Button -->
    <div class="px-4 py-4 border-b border-gray-800 flex items-center justify-between relative">
      <transition name="fade" mode="out-in">
        <div v-if="!isCollapsed" key="full-logo" class="flex flex-col items-center gap-2 w-full">
          <img src="/images/logo-tik-polri.png" alt="Logo TIK POLRI" class="w-20 h-auto rounded-sm" />
          <div class="flex flex-col items-center py-3">
            <span class="font-bold text-sm text-white leading-tight">SIMABSEN</span>
          </div>
        </div>
        <div v-else key="collapsed-logo" class="flex justify-center w-full">
          <img src="/images/logo-tik-polri.png" alt="Logo TIK POLRI" class="w-12 h-auto rounded-sm" />
        </div>
      </transition>

      <!-- Mobile Close Button -->
      <button 
        @click="$emit('update:sidebarOpen', false)"
        class="md:hidden absolute right-4 top-4 text-gray-400 hover:text-white transition-colors p-1 rounded-md hover:bg-gray-800"
      >
        <XIcon class="w-6 h-6" />
      </button>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 px-3 py-4 overflow-y-auto">
      <span v-if="!isCollapsed" class="font-medium text-sm text-white mb-3 block text-center">Menu Utama</span>
      <ul class="space-y-2">
        <li v-for="item in menuItems" :key="item.name">
          <component
            :is="item.component"
            :href="item.href"
            :class="[
              'flex items-center gap-3 px-3 py-3 rounded-xl transition-all duration-300 group relative',
              isCurrentPage(item.href) 
                ? 'bg-[#dc2626] text-white font-semibold shadow-md' 
                : 'hover:bg-[#dc2626]/20 text-gray-300 hover:text-white'
            ]"
            @click="handleMenuClick"
          >
            <component 
              :is="item.icon" 
              class="w-5 h-5 flex-shrink-0"
              :class="{ 'mx-auto': isCollapsed }"
            />
            
            <transition name="fade">
              <span v-if="!isCollapsed" class="text-sm font-medium">
                {{ item.name }}
              </span>
            </transition>
            
            <!-- Active indicator -->
            <div 
              v-if="isCurrentPage(item.href) && !isCollapsed"
              class="absolute right-0 top-0 bottom-0 w-1 bg-white rounded-l-full"
            ></div>
            
            <!-- Tooltip for collapsed state -->
            <div 
              v-if="isCollapsed" 
              class="absolute left-full ml-3 px-3 py-2 bg-[#111827] text-white text-sm font-medium rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-50"
            >
              {{ item.name }}
              <div class="absolute top-1/2 -left-1 transform -translate-y-1/2 w-2 h-2 bg-[#111827] rotate-45"></div>
            </div>
          </component>
        </li>
      </ul>
    </nav>
    
    <!-- Sidebar Footer -->
    <div class="px-3 py-4 border-t border-gray-800">
      <button 
        @click="logout"
        class="flex items-center gap-3 w-full px-3 py-3 rounded-xl text-gray-300 hover:bg-red-900/30 hover:text-white transition-colors duration-200"
        :class="{ 'justify-center': isCollapsed }"
      >
        <LogOutIcon class="w-5 h-5 flex-shrink-0" />
        <transition name="fade">
          <span v-if="!isCollapsed" class="text-sm font-medium">Keluar</span>
        </transition>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { 
  LayoutDashboardIcon, 
  FileTextIcon, 
  UsersIcon,
  SettingsIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  LogOutIcon,
  XIcon
} from 'lucide-vue-next';

// Props
const props = defineProps({
  sidebarOpen: {
    type: Boolean,
    default: true
  }
});

// Emits
const emit = defineEmits(['update:sidebarOpen', 'toggle-collapse']);

// State
const isCollapsed = ref(false);

// Menu items for SuperAdmin
const menuItems = [
  {
    name: 'Dashboard',
    href: '/superadmin/dashboard',
    icon: LayoutDashboardIcon,
    component: 'a'
  },
  {
    name: 'Kelola Pegawai',
    href: '/superadmin/pegawai',
    icon: UsersIcon,
    component: 'a'
  },
  {
    name: 'Kelola Admin',
    href: '/superadmin/admin',
    icon: UsersIcon,
    component: 'a'
  },
   {
    name: 'Laporan Global',
    href: '/superadmin/laporan',
    icon: FileTextIcon,
    component: 'a'
  },
  {
    name: 'Pengaturan Sistem',
    href: '/superadmin/settings',
    icon: SettingsIcon,
    component: 'a'
  }
];

// Computed
const currentPage = computed(() => {
  const url = window.location.pathname;
  return menuItems.find(item => item.href === url)?.name || '';
});

// Methods
const toggleCollapse = () => {
  isCollapsed.value = !isCollapsed.value;
  emit('toggle-collapse', isCollapsed.value);
};

const isCurrentPage = (href) => {
  return window.location.pathname === href;
};

const handleMenuClick = () => {
  // Close sidebar on mobile after clicking a menu item
  if (window.innerWidth < 768) {
    emit('update:sidebarOpen', false);
  }
};

const logout = () => {
  router.post('/logout');
};

// Watch for route changes to update active state
router.on('navigate', () => {
  // Force re-render to update active state
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #111827;
}

::-webkit-scrollbar-thumb {
  background: #333333;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #444444;
}
</style>