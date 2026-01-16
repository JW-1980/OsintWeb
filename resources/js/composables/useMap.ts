import L from 'leaflet';
import 'leaflet-draw';
import { Ref, onUnmounted } from 'vue';
import { Event, ControlZone } from '@/types';

export interface MapOptions {
  center?: [number, number];
  zoom?: number;
  minZoom?: number;
  maxZoom?: number;
}

export function useMap(container: Ref<HTMLElement | null>, options: MapOptions = {}) {
  let map: L.Map | null = null;
  let drawnItems: L.FeatureGroup | null = null;

  const defaultOptions: MapOptions = {
    center: [48.3794, 31.1656],
    zoom: 6,
    minZoom: 2,
    maxZoom: 18,
    ...options
  };

  function initMap(): L.Map | null {
    if (!container.value) return null;

    map = L.map(container.value, {
      center: defaultOptions.center,
      zoom: defaultOptions.zoom,
      minZoom: defaultOptions.minZoom,
      maxZoom: defaultOptions.maxZoom
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    drawnItems = new L.FeatureGroup();
    map.addLayer(drawnItems);

    return map;
  }

  function setBasemap(type: 'osm' | 'satellite' | 'terrain'): void {
    if (!map) return;

    map.eachLayer((layer) => {
      if (layer instanceof L.TileLayer) {
        map?.removeLayer(layer);
      }
    });

    let tileLayerUrl = '';
    let attribution = '';

    switch (type) {
      case 'satellite':
        tileLayerUrl = 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}';
        attribution = '&copy; Esri';
        break;
      case 'terrain':
        tileLayerUrl = 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png';
        attribution = '&copy; OpenTopoMap';
        break;
      case 'osm':
      default:
        tileLayerUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
        attribution = '&copy; OpenStreetMap contributors';
    }

    L.tileLayer(tileLayerUrl, { attribution }).addTo(map);
  }

  function addEventMarker(event: Event, onClick?: (event: Event) => void): L.Marker | null {
    if (!map || !event.coordinates) return null;

    const [lng, lat] = event.coordinates.coordinates;
    const icon = getEventIcon(event.event_type);

    const marker = L.marker([lat, lng], { icon }).addTo(map);

    if (onClick) {
      marker.on('click', () => onClick(event));
    }

    marker.bindPopup(`
      <div class="event-popup">
        <h3>${event.title}</h3>
        <p><strong>Type:</strong> ${event.event_type}</p>
        <p><strong>Date:</strong> ${new Date(event.occurred_at).toLocaleDateString()}</p>
        ${event.description ? `<p>${event.description}</p>` : ''}
      </div>
    `);

    return marker;
  }

  function addControlZone(zone: ControlZone, onClick?: (zone: ControlZone) => void): L.Polygon | null {
    if (!map) return null;

    const coordinates = zone.geometry.type === 'Polygon' && zone.geometry.coordinates[0]
      ? zone.geometry.coordinates[0].map(coord => [coord[1], coord[0]] as [number, number])
      : [];

    const polygon = L.polygon(coordinates, {
      color: zone.controller?.color_hex || '#3388ff',
      fillColor: zone.controller?.color_hex || '#3388ff',
      fillOpacity: zone.control_type === 'contested' ? 0.3 : 0.5,
      weight: 2
    }).addTo(map);

    if (onClick) {
      polygon.on('click', () => onClick(zone));
    }

    polygon.bindPopup(`
      <div class="zone-popup">
        <h3>${zone.name}</h3>
        <p><strong>Controller:</strong> ${zone.controller?.name || 'Unknown'}</p>
        <p><strong>Control:</strong> ${zone.control_type}</p>
        <p><strong>Area:</strong> ${zone.area_km2?.toFixed(2) || 'N/A'} km²</p>
      </div>
    `);

    return polygon;
  }

  function enableDrawing(onCreated?: (layer: L.Layer) => void): void {
    if (!map || !drawnItems) return;

    const drawControl = new L.Control.Draw({
      edit: {
        featureGroup: drawnItems as L.FeatureGroup
      },
      draw: {
        polygon: {
          allowIntersection: false,
          showArea: true
        },
        polyline: {},
        rectangle: {},
        circle: false,
        marker: {},
        circlemarker: false
      }
    } as L.Control.DrawConstructorOptions);

    map.addControl(drawControl);

    if (onCreated) {
      map.on(L.Draw.Event.CREATED, (event: any) => {
        const layer = event.layer;
        drawnItems?.addLayer(layer);
        onCreated(layer);
      });
    }
  }

  function flyTo(lat: number, lng: number, zoom?: number): void {
    if (!map) return;
    map.flyTo([lat, lng], zoom || map.getZoom(), {
      duration: 1.5
    });
  }

  function fitBounds(bounds: L.LatLngBoundsExpression): void {
    if (!map) return;
    map.fitBounds(bounds, { padding: [50, 50] });
  }

  function getEventIcon(eventType: string): L.DivIcon {
    const iconColors: Record<string, string> = {
      combat_engagement: '#F44336',
      airstrike: '#E91E63',
      artillery_strike: '#9C27B0',
      missile_strike: '#673AB7',
      equipment_destroyed: '#FF5722',
      equipment_captured: '#FF9800',
      equipment_abandoned: '#795548',
      equipment_sighting: '#00BCD4',
      troop_movement: '#009688',
      convoy_spotted: '#8BC34A',
      infrastructure_damage: '#607D8B',
      fortification: '#455A64',
      civilian_casualties: '#B71C1C',
      evacuation: '#1565C0'
    };

    const color = iconColors[eventType] || '#3388ff';

    return L.divIcon({
      className: 'custom-marker',
      html: `
        <div style="
          background-color: ${color};
          width: 20px;
          height: 20px;
          border-radius: 50%;
          border: 2px solid white;
          box-shadow: 0 2px 4px rgba(0,0,0,0.3);
        "></div>
      `,
      iconSize: [20, 20],
      iconAnchor: [10, 10]
    });
  }

  function destroy(): void {
    if (map) {
      map.remove();
      map = null;
    }
  }

  onUnmounted(() => {
    destroy();
  });

  return {
    map,
    initMap,
    setBasemap,
    addEventMarker,
    addControlZone,
    enableDrawing,
    flyTo,
    fitBounds,
    destroy
  };
}
