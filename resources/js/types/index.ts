import type { Polygon, MultiPolygon } from 'geojson';

export interface User {
  id: number;
  uuid: string;
  name: string;
  email: string;
  role: 'admin' | 'analyst' | 'contributor' | 'viewer';
  organization?: string;
  avatar_url?: string;
  status?: 'active' | 'banned' | 'pending';
  last_active_at?: string;
  events_count?: number;
  contributions?: number;
  logins_count?: number;
  created_at: string;
  updated_at: string;
}

export interface Country {
  id: number;
  uuid: string;
  name: string;
  iso_code_alpha2: string;
  iso_code_alpha3: string;
  flag_url?: string;
  created_at: string;
  updated_at: string;
}

export type ActorType =
  | 'STATE'
  | 'SEPARATIST'
  | 'INSURGENT'
  | 'TERRORIST'
  | 'MILITIA'
  | 'PMC'
  | 'CARTEL'
  | 'REBEL'
  | 'ETHNIC_MILITIA'
  | 'GOVERNMENT_FORCES'
  | 'COALITION'
  | 'PROXY';

export type ActivityLevel = 'high' | 'medium' | 'low' | 'inactive';

export interface Actor {
  id: number;
  uuid: string;
  name: string;
  short_name?: string;
  alias_names?: string[];
  actor_type: ActorType;
  country_id?: number;
  country?: Country;
  primary_region?: string;
  operational_areas?: string[];
  is_state_actor: boolean;
  is_designated_terrorist: boolean;
  designations?: Record<string, boolean>;
  is_active_in_conflict: boolean;
  activity_level: ActivityLevel;
  last_activity_date?: string;
  autocomplete_priority: number;
  priority_score: number;
  logo_url?: string;
  flag_url?: string;
  color_hex?: string;
  icon?: string;
  description?: string;
  founded_date?: string;
  dissolved_date?: string;
  successor_id?: number;
  parent_organization_id?: number;
  wikipedia_url?: string;
  official_website?: string;
  created_at: string;
  updated_at: string;
  deleted_at?: string;
}

export type ConflictType =
  | 'CIVIL_WAR'
  | 'INTERSTATE'
  | 'INSURGENCY'
  | 'TERRORISM'
  | 'ETHNIC_CONFLICT'
  | 'BORDER_DISPUTE'
  | 'PROXY_WAR'
  | 'OTHER';

export type IntensityLevel = 'high' | 'medium' | 'low' | 'frozen';

export interface Conflict {
  id: number;
  uuid: string;
  name: string;
  short_name?: string;
  alias_names?: string[];
  conflict_type?: ConflictType;
  intensity_level: IntensityLevel;
  primary_country_id?: number;
  primary_country?: Country;
  affected_countries?: number[];
  region?: string;
  start_date: string;
  end_date?: string;
  is_active: boolean;
  estimated_casualties?: {
    military?: number;
    civilian?: number;
    total?: number;
    last_updated?: string;
  };
  description?: string;
  background?: string;
  wikipedia_url?: string;
  created_at: string;
  updated_at: string;
  deleted_at?: string;
}

export interface Faction {
  id: number;
  uuid: string;
  name: string;
  short_name?: string;
  color_hex: string;
  icon?: string;
  description?: string;
  created_at: string;
  updated_at: string;
}

export type EventType =
  | 'combat_engagement'
  | 'airstrike'
  | 'artillery_strike'
  | 'missile_strike'
  | 'equipment_destroyed'
  | 'equipment_captured'
  | 'equipment_abandoned'
  | 'equipment_sighting'
  | 'troop_movement'
  | 'convoy_spotted'
  | 'infrastructure_damage'
  | 'fortification'
  | 'civilian_casualties'
  | 'evacuation'
  | 'territory_change'
  | 'other';

export type ConfidenceLevel = 'confirmed' | 'likely' | 'unconfirmed';

export interface Event {
  id: number;
  uuid: string;
  event_type: EventType;
  title: string;
  description?: string;
  occurred_at: string;
  coordinates?: {
    type: 'Point';
    coordinates: [number, number];
  };
  location_name?: string;
  actor_id?: number;
  actor?: Actor;
  conflict_id?: number;
  conflict?: Conflict;
  confidence_level: ConfidenceLevel;
  source_urls?: string[];
  media_urls?: string[];
  verified: boolean;
  verified_by_id?: number;
  verified_at?: string;
  created_by_id: number;
  created_by?: User;
  created_at: string;
  updated_at: string;
  deleted_at?: string;
  metadata?: Record<string, any>;
}

export interface Equipment {
  id: number;
  uuid: string;
  name: string;
  equipment_type: string;
  category: string;
  manufacturer?: string;
  origin_country_id?: number;
  origin_country?: Country;
  description?: string;
  specifications?: Record<string, any>;
  image_url?: string;
  created_at: string;
  updated_at: string;
}

export type ControlType = 'full' | 'contested' | 'claimed';

export interface ControlZone {
  id: number;
  uuid: string;
  name: string;
  geometry: Polygon | MultiPolygon;
  controller_id: number;
  controller?: Actor;
  control_type: ControlType;
  valid_from: string;
  valid_to?: string;
  source_url?: string;
  confidence: ConfidenceLevel;
  notes?: string;
  area_km2?: number;
  created_by_id: number;
  created_at: string;
  updated_at: string;
  deleted_at?: string;
}

export interface TimelineState {
  currentDate: Date;
  startDate: Date;
  endDate: Date;
  isPlaying: boolean;
  playbackSpeed: number;
  activeFilters: {
    eventTypes: EventType[];
    actors: number[];
    conflicts: number[];
    equipmentTypes: string[];
  };
}

export interface MapState {
  center: [number, number];
  zoom: number;
  basemap: 'osm' | 'satellite' | 'terrain';
  activeLayers: {
    events: boolean;
    zones: boolean;
    equipment: boolean;
    heatmap: boolean;
  };
  selectedEvent?: Event;
  selectedZone?: ControlZone;
}

export interface PaginationMeta {
  current_page: number;
  from: number;
  last_page: number;
  per_page: number;
  to: number;
  total: number;
}

export interface PaginatedResponse<T> {
  data: T[];
  meta: {
    pagination: PaginationMeta;
  };
  links: {
    first: string;
    last: string;
    prev?: string;
    next?: string;
  };
}

export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
}

export interface EventFilters {
  event_types?: EventType[];
  actor_ids?: number[];
  conflict_ids?: number[];
  date_from?: string;
  date_to?: string;
  confidence_levels?: ConfidenceLevel[];
  verified?: boolean;
  search?: string;
}

export interface LegendItem {
  id: string | number;
  name: string;
  color: string;
  icon?: string;
  visible: boolean;
  type: 'actor' | 'event' | 'equipment';
}
