<template>
  <div class="flex h-screen bg-[#F5F6FA] font-sans">
    <!-- Sidebar -->
    <SuperAdminSidebar 
      :sidebar-open="sidebarOpen"
      @update:sidebarOpen="sidebarOpen = $event"
      @toggle-collapse="handleSidebarCollapse"
    />
    
    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden transition-all duration-300" :class="sidebarCollapsed ? 'md:ml-20' : 'md:ml-64'">
      <!-- Header -->
      <SuperAdminHeader 
        title="Kelola User"
        :user-profile-pic="$page.props.auth.user.profile_pict_url"
        @toggle-sidebar="toggleSidebar"
      />

      <main class="flex-1 overflow-y-auto py-7">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <!-- Success/Error Messages -->
          <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-800 rounded">
            {{ $page.props.flash.success }}
          </div>
          <div v-if="$page.props.flash && $page.props.flash.error" class="mb-4 p-4 bg-red-100 text-red-800 rounded">
            {{ $page.props.flash.error }}
          </div>

          <!-- Page Title -->
          <div class="mb-6">
            <h2 class="text-xl md:text-2xl font-bold text-gray-900 flex items-center gap-2">
              <UsersIcon class="text-red-600 w-6 h-6 md:w-8 md:h-8" />
              Manajemen User
            </h2>
            <p class="text-sm md:text-base text-gray-600 mt-1 md:mt-2">
              Kelola data pegawai dan admin dalam satu tampilan terintegrasi.
            </p>
          </div>

          <!-- Tabs & Controls Toolbar -->
          <div class="flex flex-col lg:flex-row justify-between items-end border-b border-gray-200 mb-6 gap-4">
            <!-- Tabs -->
            <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
              <button
                @click="switchTab('pegawai')"
                :class="[
                  activeTab === 'pegawai'
                    ? 'border-red-500 text-red-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2'
                ]"
              >
                <UsersIcon class="w-5 h-5" />
                Pegawai
              </button>
              <button
                @click="switchTab('admin')"
                :class="[
                  activeTab === 'admin'
                    ? 'border-red-500 text-red-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2'
                ]"
              >
                <ShieldIcon class="w-5 h-5" />
                Admin
              </button>
            </nav>

            <!-- Controls: Search + Add Button -->
            <div class="flex flex-col md:flex-row gap-3 w-full lg:w-auto pb-2">
              <!-- Search Bar -->
              <div class="relative w-full md:w-64">
                <SearchIcon
                  class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400"
                />
                <input
                  v-model="searchQuery"
                  type="text"
                  :placeholder="activeTab === 'pegawai' ? 'Cari pegawai...' : 'Cari admin...'"
                  class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg w-full text-sm"
                  @input="handleSearch"
                />
              </div>

              <!-- Add Button -->
              <!-- Add Button (Only for Admin tab) -->
              <button
                v-if="activeTab === 'admin'"
                @click="openCreateModal"
                class="px-4 py-2 bg-[#C62828] text-white rounded-lg hover:bg-[#b71c1c] transition-all duration-300 flex items-center justify-center gap-2 text-sm whitespace-nowrap"
              >
                <PlusCircleIcon class="h-5 w-5" />
                Tambah Admin
              </button>
            </div>
          </div>

          <!-- Content Area -->
          <div class="bg-white shadow-md rounded-2xl overflow-hidden">
            <!-- Pegawai Table -->
            <div v-if="activeTab === 'pegawai'" class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-[#ad1f32] border-b border-gray-400">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider whitespace-nowrap">Nama</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider whitespace-nowrap">Email</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider whitespace-nowrap">Jabatan</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider whitespace-nowrap">Status</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-white uppercase tracking-wider whitespace-nowrap">Aksi</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50 transition-all duration-300">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div v-if="user.profile_pict_url" class="w-10 h-10 rounded-full overflow-hidden mr-3 flex-shrink-0">
                          <img :src="user.profile_pict_url" :alt="user.name" class="w-full h-full object-cover">
                        </div>
                        <div v-else class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-3 flex-shrink-0">
                          <UserIcon class="h-5 w-5 text-gray-500" />
                        </div>
                        <div>
                          <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                          <div class="text-sm text-gray-500">{{ user.nrp }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ user.email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ user.jabatan || '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="user.status === 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full">
                        {{ user.status === 'aktif' ? 'Aktif' : 'Nonaktif' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center align-middle">
                      <div class="inline-flex justify-center items-center space-x-3">
                        <button @click="viewDetail(user)" class="text-blue-600 hover:text-blue-800 p-1 rounded transition-all duration-300" title="Detail">
                          <EyeIcon class="h-5 w-5" />
                        </button>
                        <button @click="editUser(user)" class="text-[#C62828] hover:text-[#b71c1c] p-1 rounded transition-all duration-300" title="Edit">
                          <EditIcon class="h-5 w-5" />
                        </button>
                        <button @click="openDeleteModal(user)" class="text-gray-600 hover:text-gray-800 p-1 rounded transition-all duration-300" title="Hapus">
                          <TrashIcon class="h-5 w-5" />
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="users.data.length === 0">
                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada data pegawai tersedia</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Admin Table -->
            <div v-if="activeTab === 'admin'" class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-[#ad1f32] border-b border-gray-400">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider whitespace-nowrap">Nama</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider whitespace-nowrap">Email</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider whitespace-nowrap">Jabatan</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider whitespace-nowrap">Status</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-white uppercase tracking-wider whitespace-nowrap">Aksi</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="admin in admins.data" :key="admin.id" class="hover:bg-gray-50 transition-all duration-300">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div v-if="admin.profile_pict_url" class="w-10 h-10 rounded-full overflow-hidden mr-3 flex-shrink-0">
                          <img :src="admin.profile_pict_url" :alt="admin.name" class="w-full h-full object-cover">
                        </div>
                        <div v-else class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-3 flex-shrink-0">
                          <UserIcon class="h-5 w-5 text-gray-500" />
                        </div>
                        <div class="text-sm font-medium text-gray-900">{{ admin.name }}</div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ admin.email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ admin.jabatan || '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="admin.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full">
                        {{ admin.status === 'active' ? 'Aktif' : 'Nonaktif' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center align-middle">
                      <div class="inline-flex justify-center items-center space-x-3">
                        <button @click="viewDetail(admin)" class="text-blue-600 hover:text-blue-800 p-1 rounded transition-all duration-300" title="Detail">
                          <EyeIcon class="h-5 w-5" />
                        </button>
                        <button @click="editAdmin(admin)" class="text-[#C62828] hover:text-[#b71c1c] p-1 rounded transition-all duration-300" title="Edit">
                          <EditIcon class="h-5 w-5" />
                        </button>
                        <button 
                          @click="toggleAdminStatus(admin)" 
                          :class="admin.status === 'active' ? 'text-red-500 hover:text-red-700' : 'text-green-600 hover:text-green-800'"
                          class="p-1 rounded transition-all duration-300" 
                          :title="admin.status === 'active' ? 'Nonaktifkan' : 'Aktifkan'"
                        >
                          <PowerIcon class="h-5 w-5" />
                        </button>
                        <button 
                          @click="openResetPasswordModal(admin)"
                          class="text-orange-600 hover:text-orange-800 p-1 rounded transition-all duration-300"
                          title="Reset Password"
                        >
                          <KeyIcon class="h-5 w-5" />
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="admins.data.length === 0">
                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada data admin tersedia</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination (Shared) -->
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
               <div class="flex-1 flex justify-between sm:hidden">
                    <a :href="currentData.prev_page_url" 
                       :class="currentData.prev_page_url ? 'relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50' : 'relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-300 bg-white cursor-not-allowed'">
                        Sebelumnya
                    </a>
                    <a :href="currentData.next_page_url" 
                       :class="currentData.next_page_url ? 'ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50' : 'ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-300 bg-white cursor-not-allowed'">
                        Berikutnya
                    </a>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Menampilkan
                            <span class="font-medium">{{ currentData.from }}</span>
                            sampai
                            <span class="font-medium">{{ currentData.to }}</span>
                            dari
                            <span class="font-medium">{{ currentData.total }}</span>
                            hasil
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <template v-for="(link, index) in currentData.links" :key="index">
                                <span v-if="link.url === null" 
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 cursor-not-allowed">
                                    {{ link.label }}
                                </span>
                                <a v-else
                                    :href="link.url"
                                    @click.prevent="fetchPage(link.url)"
                                    :class="link.active ? 'z-10 bg-red-50 border-red-500 text-red-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"
                                    class="relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                    {{ link.label }}
                                </a>
                            </template>
                        </nav>
                    </div>
                </div>
            </div>
          </div>
        </div>
      </main>

      <!-- Modals -->
      <!-- Create/Edit User Modal (Pegawai) -->
      <!-- Edit User Modal (Pegawai) - Create removed as per request -->
      <Modal :show="showUserModal" @close="closeModal">
        <div class="p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-2">
            Edit Pegawai
          </h3>
          <p class="text-sm text-gray-500 mb-6">
            Ubah informasi pegawai yang terdaftar. Field NRP tidak dapat diubah setelah disimpan.
          </p>
          <form @submit.prevent="submitUserForm" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <InputLabel for="nrp" value="NRP" />
                <TextInput 
                  id="nrp" 
                  type="text" 
                  class="mt-1 block w-full" 
                  :class="{'bg-gray-100 cursor-not-allowed': userForm.nrp}"
                  v-model="userForm.nrp" 
                  :disabled="!!userForm.nrp"
                />
                <InputError class="mt-2" :message="userForm.errors.nrp" />
              </div>
              <div>
                <InputLabel for="name" value="Nama Lengkap" />
                <TextInput id="name" type="text" class="mt-1 block w-full" v-model="userForm.name" required />
                <InputError class="mt-2" :message="userForm.errors.name" />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <InputLabel for="email" value="Email" />
                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="userForm.email" required />
                <InputError class="mt-2" :message="userForm.errors.email" />
              </div>
               <div>
                <InputLabel for="no_hp" value="No. HP" />
                <TextInput id="no_hp" type="text" class="mt-1 block w-full" v-model="userForm.no_hp" />
                <InputError class="mt-2" :message="userForm.errors.no_hp" />
              </div>
            </div>

             <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <InputLabel for="pangkat" value="Pangkat" />
                <TextInput id="pangkat" type="text" class="mt-1 block w-full" v-model="userForm.pangkat" />
                <InputError class="mt-2" :message="userForm.errors.pangkat" />
              </div>
              <div>
                <InputLabel for="jabatan" value="Jabatan" />
                <TextInput id="jabatan" type="text" class="mt-1 block w-full" v-model="userForm.jabatan" />
                <InputError class="mt-2" :message="userForm.errors.jabatan" />
              </div>
            </div>

             <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-end">
              <div>
                <InputLabel for="status" value="Status" />
                <select id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500" v-model="userForm.status">
                  <option value="aktif">Aktif</option>
                  <option value="nonaktif">Nonaktif</option>
                </select>
                <InputError class="mt-2" :message="userForm.errors.status" />
              </div>
              <div class="flex justify-end gap-4 h-10"> <!-- H-10 aligns with input height -->
                <SecondaryButton @click="closeModal" class="w-full justify-center">Batal</SecondaryButton>
                <PrimaryButton :disabled="userForm.processing" class="w-full justify-center">Update</PrimaryButton>
              </div>
            </div>
          </form>
        </div>
      </Modal>

      <!-- Create/Edit Admin Modal -->
      <Modal :show="showAdminModal" @close="closeModal">
        <div class="p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            {{ editingAdmin ? 'Edit Admin' : 'Tambah Admin' }}
          </h3>
          <form @submit.prevent="submitAdminForm" class="space-y-6">
            <div v-if="!editingAdmin">
               <InputLabel for="admin_nrp" value="NRP Pegawai" />
               <TextInput id="admin_nrp" type="text" class="mt-1 block w-full" v-model="adminForm.nrp" required placeholder="Masukkan NRP Pegawai" />
               <p class="text-sm text-gray-500 mt-1">Masukkan NRP pegawai yang ingin dijadikan Admin.</p>
               <InputError class="mt-2" :message="adminForm.errors.nrp" />
            </div>
            <div v-else class="space-y-6">
              <p class="text-sm text-gray-500 -mt-4 mb-4">
                Ubah informasi admin yang terdaftar. Field NRP tidak dapat diubah setelah disimpan.
              </p>
               <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                 <div>
                  <InputLabel for="admin_nrp_edit" value="NRP" />
                  <TextInput 
                    id="admin_nrp_edit" 
                    type="text" 
                    class="mt-1 block w-full bg-gray-100 cursor-not-allowed" 
                    v-model="adminForm.nrp" 
                    disabled
                  />
                  <InputError class="mt-2" :message="adminForm.errors.nrp" />
                </div>
                 <div>
                 <InputLabel for="admin_name" value="Nama Lengkap" />
                 <TextInput id="admin_name" type="text" class="mt-1 block w-full" v-model="adminForm.name" required />
                 <InputError class="mt-2" :message="adminForm.errors.name" />
               </div>
               </div>

               <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
               <div>
                 <InputLabel for="admin_email" value="Email" />
                 <TextInput id="admin_email" type="text" class="mt-1 block w-full" v-model="adminForm.email" required />
                 <InputError class="mt-2" :message="adminForm.errors.email" />
               </div>
                <div>
                 <InputLabel for="admin_no_hp" value="No. HP" />
                 <TextInput id="admin_no_hp" type="text" class="mt-1 block w-full" v-model="adminForm.no_hp" />
                 <InputError class="mt-2" :message="adminForm.errors.no_hp" />
               </div>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
               <div>
                 <InputLabel for="admin_pangkat" value="Pangkat" />
                 <TextInput id="admin_pangkat" type="text" class="mt-1 block w-full" v-model="adminForm.pangkat" />
                 <InputError class="mt-2" :message="adminForm.errors.pangkat" />
               </div>
               <div>
                 <InputLabel for="admin_jabatan" value="Jabatan" />
                 <TextInput id="admin_jabatan" type="text" class="mt-1 block w-full" v-model="adminForm.jabatan" />
                 <InputError class="mt-2" :message="adminForm.errors.jabatan" />
               </div>
              </div>


               <!-- Status field removed for edit mode as requested -->
            </div>
            <div class="flex justify-end gap-4">
              <SecondaryButton @click="closeModal">Batal</SecondaryButton>
              <PrimaryButton :disabled="adminForm.processing">{{ editingAdmin ? 'Update' : 'Simpan' }}</PrimaryButton>
            </div>
          </form>
        </div>
      </Modal>

      <!-- Detail Modal -->
      <Modal :show="showDetailModal" @close="closeDetailModal">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-xl font-bold text-gray-900">Detail {{ activeTab === 'pegawai' ? 'Pegawai' : 'Admin' }}</h3>
              <button @click="closeDetailModal" class="text-gray-500 hover:text-gray-700">
                <XIcon class="h-6 w-6" />
              </button>
            </div>
            <div class="flex flex-col items-center mb-6">
                 <img 
                    :src="detailData.profile_pict_url || '/images/profile.png'" 
                    :alt="detailData.name"
                    class="w-32 h-32 rounded-full ring-4 ring-red-500 object-cover mb-4"
                    @error="handleImageError"
                  />
                  <h4 class="text-lg font-bold text-gray-900">{{ detailData.name }}</h4>
                  <p class="text-gray-600">{{ detailData.jabatan }}</p>
                  <div class="mt-2">
                     <span :class="(detailData.status === 'aktif' || detailData.status === 'active') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-3 py-1 text-sm font-semibold rounded-full">
                        {{ (detailData.status === 'aktif' || detailData.status === 'active') ? 'Aktif' : 'Nonaktif' }}
                     </span>
                  </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Email</p>
                    <p class="font-medium">{{ detailData.email }}</p>
                </div>
                 <div>
                    <p class="text-sm text-gray-500">NRP</p>
                    <p class="font-medium">{{ detailData.nrp || '-' }}</p>
                </div>
                 <div>
                    <p class="text-sm text-gray-500">NIP</p>
                    <p class="font-medium">{{ detailData.nip || '-' }}</p>
                </div>
                 <div>
                    <p class="text-sm text-gray-500">Pangkat</p>
                    <p class="font-medium">{{ detailData.pangkat || '-' }}</p>
                </div>
                 <div>
                    <p class="text-sm text-gray-500">No. HP</p>
                    <p class="font-medium">{{ detailData.no_hp || '-' }}</p>
                </div>
            </div>
             <div class="mt-6 flex justify-end">
                 <SecondaryButton @click="closeDetailModal">Tutup</SecondaryButton>
             </div>
        </div>
      </Modal>

       <!-- Delete Confirmation Modal -->
      <ConfirmModal
        :open="showDeleteModal"
        :title="activeTab === 'pegawai' ? 'Hapus Pegawai' : 'Hapus Admin'"
        :message="activeTab === 'pegawai' ? 'Apakah Anda yakin ingin menghapus pegawai ini?' : 'Apakah Anda yakin ingin menghapus admin ini?'"
        type="danger"
        confirm-text="Hapus"
        cancel-text="Batal"
        @close="showDeleteModal = false"
        @confirm="confirmDelete"
      />

       <!-- Reset Password Confirmation Modal -->
      <ConfirmModal
        :open="showResetPasswordModal"
        title="Reset Password Admin"
        message="Apakah Anda yakin ingin mereset password admin ini menjadi default ('password')? Tindakan ini tidak dapat dibatalkan."
        type="warning"
        confirm-text="Reset Password"
        cancel-text="Batal"
        @close="showResetPasswordModal = false"
        @confirm="confirmResetPassword"
      />

    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import SuperAdminSidebar from '@/Components/SuperAdminSidebar.vue';
import SuperAdminHeader from '@/Components/SuperAdminHeader.vue';
import Modal from '@/Components/Modal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { 
    UsersIcon, 
    ShieldIcon, 
    SearchIcon, 
    PlusCircleIcon, 
    EyeIcon, 
    EditIcon, 
    TrashIcon, 
    UserIcon,
    XIcon,
    PowerIcon,
    KeyIcon
} from 'lucide-vue-next';
import debounce from 'lodash/debounce';

const props = defineProps({
    users: Object,
    admins: Object,
    filters: Object,
});

const page = usePage();
const sidebarOpen = ref(true);
const sidebarCollapsed = ref(false);
const activeTab = ref(props.filters?.tab || 'pegawai');
const searchQuery = ref(props.filters?.search || '');

// Modals state
const showUserModal = ref(false);
const showAdminModal = ref(false);
const showDetailModal = ref(false);
const showDeleteModal = ref(false);
const showResetPasswordModal = ref(false);

const editingUser = ref(null);
const editingAdmin = ref(null);
const detailData = ref({});
const itemToDelete = ref(null);
const adminToResetPassword = ref(null);

// Forms
const userForm = useForm({
    name: '',
    email: '',
    no_hp: '',
    pangkat: '',
    nip: '',
    nrp: '',
    jabatan: '',
    status: 'aktif',
});

const adminForm = useForm({
    nrp: '',
    nip: '',
    name: '',
    email: '',
    jabatan: '',
    pangkat: '',
    no_hp: '',
    status: 'active',
});

// Computed
const currentData = computed(() => {
    return activeTab.value === 'pegawai' ? props.users : props.admins;
});

// Methods
const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const handleSidebarCollapse = (collapsed) => {
    sidebarCollapsed.value = collapsed;
};

const switchTab = (tab) => {
    activeTab.value = tab;
    searchQuery.value = ''; // Reset search on tab switch
    router.get(route('superadmin.users'), { tab: tab }, { preserveState: true, replace: true });
};

const handleSearch = debounce(() => {
    router.get(route('superadmin.users'), { 
        search: searchQuery.value,
        tab: activeTab.value 
    }, { 
        preserveState: true, 
        replace: true,
        preserveScroll: true 
    });
}, 300);

const fetchPage = (url) => {
    if (!url) return;
    router.get(url, { 
        search: searchQuery.value,
        tab: activeTab.value
    }, { preserveState: true, preserveScroll: true });
};

const openCreateModal = () => {
    // Only allow creating admins
    if (activeTab.value === 'admin') {
        editingAdmin.value = null;
        adminForm.reset();
        showAdminModal.value = true;
    }
};

const closeModal = () => {
    showUserModal.value = false;
    showAdminModal.value = false;
    showDeleteModal.value = false;
    showResetPasswordModal.value = false;
    editingUser.value = null;
    editingAdmin.value = null;
    itemToDelete.value = null;
    adminToResetPassword.value = null;
    userForm.reset();
    adminForm.reset();
};

// User (Pegawai) Actions
const editUser = (user) => {
    editingUser.value = user;
    userForm.name = user.name;
    userForm.email = user.email;
    userForm.no_hp = user.no_hp;
    userForm.pangkat = user.pangkat;
    userForm.nip = user.nip;
    userForm.nrp = user.nrp;
    userForm.jabatan = user.jabatan;
    userForm.status = user.status;
    showUserModal.value = true;
};

const submitUserForm = () => {
    if (editingUser.value) {
        userForm.patch(route('superadmin.pegawai.update', editingUser.value.id), {
            onSuccess: () => closeModal(),
        });
    }
};

// Admin Actions
const editAdmin = (admin) => {
    editingAdmin.value = admin;
    adminForm.nrp = admin.nrp || '';
    adminForm.nip = admin.nip || '';
    adminForm.name = admin.name || '';
    adminForm.email = admin.email || '';
    adminForm.jabatan = admin.jabatan || '';
    adminForm.pangkat = admin.pangkat || '';
    adminForm.no_hp = admin.no_hp || '';
    adminForm.status = admin.status || 'active';
    showAdminModal.value = true;
};

const submitAdminForm = () => {
    if (editingAdmin.value) {
        adminForm.patch(route('superadmin.admin.update', editingAdmin.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        adminForm.post(route('superadmin.admin.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const toggleAdminStatus = (admin) => {
    router.patch(route('superadmin.admin.toggle-status', admin.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Status updated
        }
    });
};

const openResetPasswordModal = (admin) => {
    adminToResetPassword.value = admin;
    showResetPasswordModal.value = true;
};

const confirmResetPassword = () => {
    if (adminToResetPassword.value) {
        router.patch(route('superadmin.admin.reset-password', adminToResetPassword.value.id), {}, {
            onSuccess: () => {
                showResetPasswordModal.value = false;
                adminToResetPassword.value = null;
            }
        });
    }
};

// Common Actions
const viewDetail = (data) => {
    detailData.value = { ...data };
    showDetailModal.value = true;
};

const closeDetailModal = () => {
    showDetailModal.value = false;
    detailData.value = {};
};

const openDeleteModal = (data) => {
    itemToDelete.value = data;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!itemToDelete.value) return;
    
    const routeName = activeTab.value === 'pegawai' ? 'superadmin.pegawai.destroy' : 'superadmin.admin.destroy';
    
    router.delete(route(routeName, itemToDelete.value.id), {
        onSuccess: () => closeModal(),
    });
};

const handleImageError = (event) => {
    event.target.src = '/images/profile.png';
};
</script>
