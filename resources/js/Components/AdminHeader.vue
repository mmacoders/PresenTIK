<template>
  <header class="flex justify-between items-center bg-white border-b border-gray-200 px-4 md:px-6 py-3 shadow-sm sticky top-0 z-20 font-sans">
    <div class="flex items-center gap-3">
      <!-- Sidebar Toggle Button -->
      <button 
        class="text-gray-600 hover:text-[#dc2626] focus:outline-none focus:ring-2 focus:ring-[#dc2626]/30 rounded-lg p-1 transition-colors duration-200 mr-2"
        @click="toggleSidebar"
        aria-label="Toggle sidebar"
      >
        <MenuIcon class="w-6 h-6" />
      </button>
      
      <!-- Breadcrumb Navigation (Desktop) -->
      <nav class="hidden md:block">
        <ol class="flex items-center space-x-1.5">
          <li>
            <a 
              :href="route('admin.dashboard')" 
              class="group flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-50 hover:bg-[#dc2626]/5 hover:text-[#dc2626] transition-all duration-200 text-gray-600 hover:shadow-sm"
            >
              <HomeIcon class="w-4 h-4 transition-transform duration-200 group-hover:scale-110" />
              <span class="text-sm font-medium">Dashboard</span>
            </a>
          </li>
          <li v-if="title !== 'Dashboard'" class="flex items-center">
            <ChevronRightIcon class="w-4 h-4 text-gray-300 mx-1" />
            <span class="px-3 py-1.5 rounded-lg bg-gradient-to-r from-[#dc2626] to-[#b91c1c] text-white text-sm font-semibold shadow-sm">
              {{ title }}
            </span>
          </li>
        </ol>
      </nav>
      
      <!-- Mobile Title - hanya untuk mobile, hidden di md+ -->
      <h1 class="text-lg font-semibold text-gray-800 block md:hidden" v-if="title !== 'Dashboard'">{{ title }}</h1>
      
      <div class="sm:hidden">
        <button 
          @click="toggleMobileMenu" 
          class="text-gray-600 hover:text-[#dc2626] focus:outline-none"
          aria-label="Toggle menu"
        >
        </button>
      </div>
    </div>
    <div class="flex items-center gap-4">
      <p class="text-sm text-gray-700 font-medium hidden md:block">{{ role }}</p>
      <div class="relative" ref="dropdownContainer">
        <button 
          class="flex items-center gap-2 focus:outline-none focus:ring-2 focus:ring-[#dc2626]/30 rounded-lg p-1 transition-colors duration-200"
          @click="toggleDropdown"
          aria-label="User menu"
        >
          <div v-if="effectiveUserProfilePic" class="w-8 h-8 rounded-full overflow-hidden">
            <img :src="effectiveUserProfilePic" :alt="effectiveUserName" class="w-full h-full object-cover">
          </div>
          <div v-else class="w-8 h-8 rounded-full bg-[#dc2626] flex items-center justify-center text-white font-semibold text-sm">
            {{ userInitials }}
          </div>
          <ChevronDownIcon class="w-4 h-4 text-gray-500 hidden md:block" />
        </button>
        <!-- Dropdown -->
        <div 
          v-if="dropdownOpen" 
          class="absolute right-0 mt-2 bg-white border border-gray-200 rounded-xl shadow-lg py-2 w-56 z-50 transition-all duration-200 origin-top-right"
          :class="{ 'opacity-100 scale-100': dropdownOpen, 'opacity-0 scale-95': !dropdownOpen }"
        >
          <div class="px-4 py-3 border-b border-gray-100">
            <p class="text-sm font-medium text-gray-900 truncate">{{ effectiveUserName }}</p>
            <p class="text-xs text-gray-500 truncate">{{ effectiveUserEmail }}</p>
          </div>
          <a 
            :href="route('admin.profil')" 
            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center"
          >
            <UserIcon class="w-4 h-4 mr-2 text-gray-500" />
            Profil
          </a>
          <button
            @click="logout"
            class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-200 flex items-center"
          >
            <LogOutIcon class="w-4 h-4 mr-2" />
            Keluar
          </button>
        </div>
      </div>
    </div>
  </header>
  
  <!-- Mobile Menu -->
  <div 
    v-if="mobileMenuOpen" 
    class="absolute top-full left-0 right-0 bg-white border-b border-gray-200 shadow-lg md:hidden z-10"
  >
    <div class="px-4 py-3">
      <!-- Mobile Breadcrumb -->
      <div class="mb-3" v-if="title !== 'Dashboard'">
        <ol class="flex items-center space-x-1.5">
          <li>
            <a :href="route('admin.dashboard')" class="flex items-center gap-1 px-2 py-1 rounded-md bg-gray-50 hover:bg-[#dc2626]/5 text-gray-600 hover:text-[#dc2626] transition-all duration-200 text-xs font-medium">
              <HomeIcon class="w-3 h-3" />
              Dashboard
            </a>
          </li>
          <li>
            <ChevronRightIcon class="w-3 h-3 text-gray-300" />
          </li>
          <li>
            <span class="px-2 py-1 rounded-md bg-gradient-to-r from-[#dc2626] to-[#b91c1c] text-white text-xs font-semibold">
              {{ mobileTitle || title }}
            </span>
          </li>
        </ol>
      </div>
      <h2 class="text-lg font-semibold text-gray-800">{{ title }}</h2>
      <p class="text-sm text-gray-600">{{ role }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { 
  MenuIcon, 
  ChevronDownIcon,
  ChevronRightIcon,
  UserIcon,
  LogOutIcon,
  XIcon,
  HomeIcon
} from 'lucide-vue-next';

// Props
const props = defineProps({
  title: {
    type: String,
    default: 'Dashboard'
  },
  role: {
    type: String,
    default: 'Admin'
  },
  userName: {
    type: String,
    default: 'Admin User'
  },
  userEmail: {
    type: String,
    default: 'admin@example.com'
  },
  userProfilePic: {
    type: String,
    default: null
  },
  mobileTitle: {
    type: String,
    default: ''
  }
});

// Get page props
const page = usePage();

// State
const dropdownOpen = ref(false);
const mobileMenuOpen = ref(false);
const dropdownContainer = ref(null);

// Computed properties
const userInitials = computed(() => {
  // First try to get name from page props (Inertia data)
  const userName = page.props.auth?.user?.name || props.userName;
  if (!userName) return 'AU'; // Default to 'AU' for Admin User
  return userName.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
});

// Use page props for user data if available, otherwise use component props
const effectiveUserProfilePic = computed(() => {
  return page.props.auth?.user?.profile_pict_url || props.userProfilePic;
});

const effectiveUserName = computed(() => {
  return page.props.auth?.user?.name || props.userName;
});

const effectiveUserEmail = computed(() => {
  return page.props.auth?.user?.email || props.userEmail;
});

// Methods
const emit = defineEmits(['toggle-sidebar']);

const toggleSidebar = () => {
  emit('toggle-sidebar');
};

const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value;
};

const toggleDropdown = () => {
  dropdownOpen.value = !dropdownOpen.value;
};

const logout = () => {
  router.post('/logout');
};

// Click outside handler
const handleClickOutside = (event) => {
  if (dropdownContainer.value && !dropdownContainer.value.contains(event.target)) {
    dropdownOpen.value = false;
  }
  
  // Close mobile menu when clicking outside
  if (mobileMenuOpen.value && !event.target.closest('header')) {
    mobileMenuOpen.value = false;
  }
};

// Close dropdowns when pressing Escape key
const handleEscapeKey = (event) => {
  if (event.key === 'Escape') {
    dropdownOpen.value = false;
    mobileMenuOpen.value = false;
  }
};

// Lifecycle
onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  document.addEventListener('keydown', handleEscapeKey);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
  document.removeEventListener('keydown', handleEscapeKey);
});
</script>