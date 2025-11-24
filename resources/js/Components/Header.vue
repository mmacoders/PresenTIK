<template>
  <header class="top-0 h-16 bg-white border-b border-gray-200 shadow-sm backdrop-blur-md z-40 flex items-center justify-between px-4 md:px-6 sticky ml-0 transition-all duration-300 ease-in-out w-full">
    <!-- Left Section -->
    <div class="flex items-center gap-3 md:gap-4">
      <!-- Sidebar Toggle Button -->
      <button 
        @click="$emit('toggle-sidebar')"
        class="p-2 rounded-lg hover:bg-gray-100 transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-[#dc2626]/30"
        aria-label="Toggle sidebar"
      >
        <MenuIcon class="w-5 h-5 text-gray-600" />
      </button>

      <!-- Breadcrumb Navigation -->
      <nav class="hidden md:block">
        <ol class="flex items-center space-x-2 text-sm">
          <li>
            <a href="/user/dashboard" class="text-gray-500 hover:text-[#dc2626] transition-colors duration-200 flex items-center">
              <HomeIcon class="w-4 h-4 mr-1" />
              Dashboard
            </a>
          </li>
          <li>
            <ChevronRightIcon class="w-4 h-4 text-gray-400" />
          </li>
          <li>
            <span class="text-[#dc2626] font-medium flex items-center">
              <span class="hidden lg:inline">{{ currentPage }}</span>
              <span class="lg:hidden">{{ mobileBreadcrumb }}</span>
            </span>
          </li>
        </ol>
      </nav>
      
      <!-- Mobile Breadcrumb -->
      <div class="md:hidden">
        <span class="text-[#dc2626] font-medium text-sm">{{ mobileBreadcrumb }}</span>
      </div>
    </div>

    <!-- Right Section -->
    <div class="flex items-center gap-3 md:gap-4">
      <!-- User Profile -->
      <div class="relative" ref="dropdownContainer">
        <button 
          @click="toggleDropdown"
          class="flex items-center gap-2 focus:outline-none focus:ring-2 focus:ring-[#dc2626]/30 rounded-lg p-1 transition-colors duration-200"
          aria-label="User menu"
        >
          <div v-if="user && user.profile_pict" class="w-8 h-8 rounded-full border-2 border-white shadow-sm overflow-hidden">
            <img 
              :src="user.profile_pict" 
              :alt="user.name + ' avatar'" 
              class="w-full h-full object-cover"
              @error="handleImageError"
            >
          </div>
          <div v-else class="w-8 h-8 rounded-full bg-gray-200 border-2 border-white shadow-sm flex items-center justify-center text-gray-700 font-semibold text-sm">
            {{ userInitials }}
          </div>
          <span v-if="user" class="hidden md:inline text-gray-700 font-medium text-sm">{{ user.name }}</span>
          <ChevronDownIcon 
            class="hidden md:inline w-4 h-4 text-gray-500 transition-transform duration-200" 
            :class="{ 'rotate-180': isDropdownOpen }"
          />
        </button>

        <!-- Dropdown Menu -->
        <div 
          v-if="isDropdownOpen"
          class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg py-2 border border-gray-100 transition-all duration-200 origin-top-right z-50"
          :class="{ 'opacity-100 scale-100': isDropdownOpen, 'opacity-0 scale-95': !isDropdownOpen }"
        >
          <div class="px-4 py-2 border-b border-gray-100">
            <p class="text-sm font-medium text-gray-900 truncate">{{ user?.name }}</p>
            <p class="text-xs text-gray-500 truncate">{{ user?.email || 'user@example.com' }}</p>
          </div>
          <a 
            href="/user/profil" 
            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center"
          >
            <UserIcon class="w-4 h-4 mr-2 text-gray-500" />
            Profil Saya
          </a>
          <button 
            @click="logout"
            class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-200 flex items-center"
          >
            <LogOutIcon class="w-4 h-4 mr-2" />
            Logout
          </button>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { MenuIcon, ChevronRightIcon, ChevronDownIcon, UserIcon, LogOutIcon, HomeIcon } from 'lucide-vue-next';

// Define props
const props = defineProps({
  user: Object,
  currentPage: String,
  mobileBreadcrumb: String,
  sidebarCollapsed: {
    type: Boolean,
    default: false
  }
});

// Computed property for header margin class - REMOVED
// const headerMarginClass = computed(() => {
//   return props.sidebarCollapsed ? 'md:ml-20' : 'md:ml-64';
// });

// Watch for window resize to update header margin
const handleWindowResize = () => {
  // This will be handled by the parent component passing the correct sidebarCollapsed prop
};

// Define emits
defineEmits(['toggle-sidebar']);

// Dropdown state
const isDropdownOpen = ref(false);
const dropdownContainer = ref(null);

// Computed property for user initials
const userInitials = computed(() => {
  if (!props.user || !props.user.name) return 'U';
  return props.user.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
});

// Toggle dropdown
const toggleDropdown = () => {
  isDropdownOpen.value = !isDropdownOpen.value;
};

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (dropdownContainer.value && !dropdownContainer.value.contains(event.target)) {
    isDropdownOpen.value = false;
  }
};

// Close dropdown when pressing Escape key
const handleEscapeKey = (event) => {
  if (event.key === 'Escape') {
    isDropdownOpen.value = false;
  }
};

// Add event listeners
onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  document.addEventListener('keydown', handleEscapeKey);
  window.addEventListener('resize', handleWindowResize);
});

// Remove event listeners
onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
  document.removeEventListener('keydown', handleEscapeKey);
  window.removeEventListener('resize', handleWindowResize);
});

// Update header margin on window resize
const updateHeaderMargin = () => {
  // Force reactivity update
  // The computed property will automatically recalculate based on the props
};

// Logout function
const logout = () => {
  router.post(route('logout'));
};

// Handle image error - fallback to initials
const handleImageError = (event) => {
  // When image fails to load, we'll hide it and show initials instead
  event.target.style.display = 'none';
  // Find the parent element and add a fallback element with initials
  const parent = event.target.closest('.rounded-full');
  if (parent) {
    // Create initials element
    const initialsElement = document.createElement('div');
    initialsElement.className = 'w-full h-full flex items-center justify-center text-gray-700 font-semibold text-sm';
    initialsElement.textContent = userInitials.value;
    parent.appendChild(initialsElement);
  }
};
</script>