<template>
  <header 
    class="bg-white border-b border-gray-200 shadow-sm sticky top-0 font-sans transition-all duration-300 z-30"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16 items-center">
        <div class="flex items-center">
          <!-- Sidebar Toggle Button (Visible on all screens) -->
          <button 
            @click="$emit('toggle-sidebar')" 
            class="p-2 rounded-md text-gray-600 hover:bg-gray-100 mr-3 focus:outline-none focus:ring-2 focus:ring-[#dc2626]/30 transition-all duration-300"
            aria-label="Toggle sidebar"
          >
            <MenuIcon class="w-6 h-6" />
          </button>
          
          <!-- Breadcrumb Navigation (Desktop) -->
          <nav class="hidden md:block">
            <ol class="flex items-center space-x-2 text-sm font-normal">
              <li>
                <a :href="route('superadmin.dashboard')" class="text-gray-500 hover:text-[#dc2626] transition-colors duration-200 flex items-center">
                  Dashboard
                </a>
              </li>
              <li>
                <ChevronRightIcon class="w-4 h-4 text-gray-400" />
              </li>
              <li>
                <a :href="route('superadmin.dashboard')" class="text-gray-500 hover:text-[#dc2626] transition-colors duration-200 flex items-center">
                  {{ title }}
                </a>
              </li>
            </ol>
          </nav>
          
          <!-- Mobile Title - Only visible on mobile -->
          <h1 class="font-semibold text-xl text-gray-800 leading-tight md:hidden">{{ mobileTitle || title }}</h1>
        </div>
        <div class="flex items-center space-x-4">
          <div class="relative">
            <button class="p-2 rounded-full hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-[#dc2626]/30">
              <BellIcon class="h-5 w-5 text-gray-600" />
            </button>
          </div>
          <div class="relative" ref="profileDropdown">
            <button @click="toggleProfileMenu" class="flex items-center space-x-2 focus:outline-none">
              <div v-if="effectiveUserProfilePic" class="h-10 w-10 rounded-full overflow-hidden">
                <img :src="effectiveUserProfilePic" :alt="'Super Admin avatar'" class="w-full h-full object-cover">
              </div>
              <div v-else class="h-10 w-10 rounded-full bg-[#dc2626] flex items-center justify-center text-white font-semibold">
                {{ userInitials }}
              </div>
              <span class="text-gray-700 hidden md:block">Super Admin</span>
              <ChevronDownIcon class="h-4 w-4 text-gray-500 hidden md:block" />
            </button>
            <!-- Profile Dropdown -->
            <div v-if="profileMenuOpen" class="origin-top-right absolute right-0 mt-2 w-48 rounded-xl shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50 border border-gray-100">
              <div class="py-1">
                <a :href="route('superadmin.profil')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center">
                  <UserIcon class="h-4 w-4 mr-2 text-gray-500" />
                  Profil
                </a>
                <button @click="logout" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-200 flex items-center">
                  <LogOutIcon class="h-4 w-4 mr-2" />
                  Keluar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>
  

</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { 
  BellIcon, 
  ChevronDownIcon, 
  ChevronRightIcon,
  UserIcon, 
  LogOutIcon, 
  MenuIcon,
  HomeIcon
} from 'lucide-vue-next';

// Define props
const props = defineProps({
  title: {
    type: String,
    default: 'Dashboard'
  },
  userProfilePic: {
    type: String,
    default: null
  },
  mobileTitle: {
    type: String,
    default: ''
  },
  isCollapsed: {
    type: Boolean,
    default: false
  }
});

// Define emits
defineEmits(['toggle-sidebar']);

// Get page props
const page = usePage();

// State
const profileMenuOpen = ref(false);
const profileDropdown = ref(null);

// Computed properties
const userInitials = computed(() => {
  // First try to get name from page props (Inertia data)
  const userName = page.props.auth?.user?.name;
  if (!userName) return 'SA'; // Default to 'SA' for Super Admin
  return userName.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
});

// Use page props for user profile picture if available, otherwise use component props
const effectiveUserProfilePic = computed(() => {
  return page.props.auth?.user?.profile_pict_url || props.userProfilePic;
});

// Methods
const toggleProfileMenu = () => {
  profileMenuOpen.value = !profileMenuOpen.value;
};

const logout = () => {
  router.post('/logout');
};

// Close profile menu when clicking outside
const handleClickOutside = (event) => {
  if (profileMenuOpen.value && profileDropdown.value && !profileDropdown.value.contains(event.target)) {
    profileMenuOpen.value = false;
  }
};

// Close dropdowns when pressing Escape key
const handleEscapeKey = (event) => {
  if (event.key === 'Escape') {
    profileMenuOpen.value = false;
  }
};

// Add event listeners
onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  document.addEventListener('keydown', handleEscapeKey);
});

// Remove event listeners
onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
  document.removeEventListener('keydown', handleEscapeKey);
});
</script>