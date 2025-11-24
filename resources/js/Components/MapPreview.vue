<template>
  <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Preview Lokasi</h3>
    <div ref="mapContainer" id="map" class="rounded-lg h-64 w-full"></div>
    <div v-if="!latitude || !longitude" class="text-center absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 rounded-lg">
      <div class="text-center">
        <MapPinIcon class="h-12 w-12 text-gray-400 mx-auto" />
        <p class="mt-2 text-gray-600">Peta lokasi kantor akan ditampilkan di sini</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { MapPinIcon } from 'lucide-vue-next';

// Delete the default marker icons that cause issues with Vite
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: new URL('leaflet/dist/images/marker-icon-2x.png', import.meta.url).href,
  iconUrl: new URL('leaflet/dist/images/marker-icon.png', import.meta.url).href,
  shadowUrl: new URL('leaflet/dist/images/marker-shadow.png', import.meta.url).href,
});

// Props
const props = defineProps({
  latitude: {
    type: [String, Number],
    default: null
  },
  longitude: {
    type: [String, Number],
    default: null
  },
  radius: {
    type: [String, Number],
    default: 100
  }
});

// Refs
const mapContainer = ref(null);
let map = null;
let marker = null;
let circle = null;

// Initialize map
const initMap = () => {
  if (!mapContainer.value) return;
  
  // Clear previous map if exists
  if (map) {
    map.remove();
  }
  
  // Check if latitude and longitude are valid
  if (!props.latitude || !props.longitude) {
    return;
  }
  
  const lat = parseFloat(props.latitude);
  const lng = parseFloat(props.longitude);
  
  // Create map
  map = L.map(mapContainer.value).setView([lat, lng], 15);
  
  // Add OpenStreetMap tiles
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
  }).addTo(map);
  
  // Add marker
  marker = L.marker([lat, lng], {
    icon: L.icon({
      iconUrl: new URL('leaflet/dist/images/marker-icon.png', import.meta.url).href,
      iconRetinaUrl: new URL('leaflet/dist/images/marker-icon-2x.png', import.meta.url).href,
      shadowUrl: new URL('leaflet/dist/images/marker-shadow.png', import.meta.url).href,
      iconSize: [25, 41],
      iconAnchor: [12, 41]
    })
  }).addTo(map);
  
  // Add circle
  circle = L.circle([lat, lng], {
    color: '#C62828',
    fillColor: 'rgba(198, 40, 40, 0.3)',
    fillOpacity: 0.5,
    radius: parseFloat(props.radius)
  }).addTo(map);
  
  // Fit bounds to show both marker and circle
  const bounds = circle.getBounds();
  map.fitBounds(bounds, { padding: [50, 50] });
};

// Update map when props change
const updateMap = async () => {
  if (!map) {
    await nextTick();
    initMap();
    return;
  }
  
  // Check if latitude and longitude are valid
  if (!props.latitude || !props.longitude) {
    return;
  }
  
  const lat = parseFloat(props.latitude);
  const lng = parseFloat(props.longitude);
  
  // Update marker position with animation
  if (marker) {
    marker.setLatLng([lat, lng]);
    map.panTo([lat, lng], {
      animate: true,
      duration: 1.0
    });
  } else {
    marker = L.marker([lat, lng]).addTo(map);
  }
  
  // Update circle
  if (circle) {
    circle.setLatLng([lat, lng]);
    circle.setRadius(parseFloat(props.radius));
  } else {
    circle = L.circle([lat, lng], {
      color: '#C62828',
      fillColor: 'rgba(198, 40, 40, 0.3)',
      fillOpacity: 0.5,
      radius: parseFloat(props.radius)
    }).addTo(map);
  }
  
  // Fit bounds to show both marker and circle
  const bounds = circle.getBounds();
  map.fitBounds(bounds, { padding: [50, 50] });
};

// Watch for prop changes
watch(() => [props.latitude, props.longitude, props.radius], () => {
  updateMap();
});

// Lifecycle
onMounted(() => {
  initMap();
});

onUnmounted(() => {
  if (map) {
    map.remove();
  }
});

// Expose methods if needed
defineExpose({
  updateMap
});
</script>

<style scoped>
@import 'leaflet/dist/leaflet.css';

#map {
  z-index: 1;
}
</style>