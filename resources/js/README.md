# OsintWeb Vue.js 3 Frontend

This directory contains the complete Vue.js 3 frontend application for OsintWeb, built with TypeScript, Composition API, and modern best practices.

## Directory Structure

```
/home/user/OsintWeb/resources/js/
├── App.vue                          # Root application component
├── app.ts                           # Main application entry point
├── components/
│   ├── events/
│   │   ├── ActorSelector.vue        # Actor autocomplete selector
│   │   ├── EventCard.vue            # Event summary card component
│   │   ├── EventForm.vue            # Create/edit event form
│   │   └── EventList.vue            # Event list with filters
│   ├── layout/
│   │   ├── AppHeader.vue            # Application header with navigation
│   │   └── AppSidebar.vue           # Sidebar with layers and filters
│   ├── map/
│   │   ├── ControlZonePolygon.vue   # Control zone polygon component
│   │   ├── DrawingTools.vue         # Leaflet drawing tools
│   │   ├── EventMarker.vue          # Event marker component
│   │   ├── MapLegend.vue            # Dynamic map legend
│   │   └── MapView.vue              # Main Leaflet map component
│   └── timeline/
│       └── TimelineBar.vue          # Timeline scrubber with playback
├── composables/
│   ├── useApi.ts                    # Axios API wrapper with auth
│   └── useMap.ts                    # Leaflet map helpers
├── router/
│   └── index.ts                     # Vue Router configuration
├── stores/
│   ├── auth.ts                      # Pinia authentication store
│   ├── events.ts                    # Pinia events store
│   └── map.ts                       # Pinia map state store
├── types/
│   └── index.ts                     # TypeScript type definitions
└── views/
    ├── Dashboard.vue                # Dashboard page
    ├── EventDetail.vue              # Event detail page
    └── MapPage.vue                  # Full map page
```

## Technology Stack

- **Vue.js 3.4+**: Progressive JavaScript framework
- **TypeScript 5.3+**: Static type checking
- **Pinia 2.1+**: State management
- **Vue Router 4.2+**: Client-side routing
- **Leaflet 1.9+**: Interactive maps
- **Leaflet Draw 1.0+**: Drawing tools for map
- **Axios**: HTTP client
- **Vite 5.0+**: Build tool and dev server

## Features Implemented

### 1. Core Application Structure
- **App.vue**: Root component with authentication check
- **app.ts**: Application entry point with Pinia and Router setup
- **Router**: Complete routing with auth guards and lazy loading

### 2. Type Definitions (types/index.ts)
Complete TypeScript interfaces for:
- User, Actor, Country, Conflict
- Event, EventType, EventFilters
- Equipment, ControlZone
- MapState, TimelineState
- PaginatedResponse, ApiError
- LegendItem

### 3. State Management (Pinia Stores)

#### auth.ts
- User authentication state
- Login, register, logout actions
- Profile management
- Role-based access control (admin, analyst, contributor, viewer)

#### events.ts
- Events list and pagination
- Event CRUD operations
- Filtering and search
- Current event state

#### map.ts
- Map center and zoom state
- Basemap selection (OSM, Satellite, Terrain)
- Layer visibility toggles
- Selected event/zone tracking
- Legend management
- Drawing mode state

### 4. Composables

#### useApi.ts
- Axios HTTP client wrapper
- Automatic authentication headers
- Request/response interceptors
- Error handling
- TypeScript generics for type-safe API calls

#### useMap.ts
- Leaflet map initialization
- Basemap switching
- Event marker rendering
- Control zone polygon rendering
- Drawing tools integration
- Map navigation (flyTo, fitBounds)
- Custom marker icons by event type

### 5. Layout Components

#### AppHeader.vue
- Navigation menu
- Search functionality
- User menu with avatar
- Role-based admin link
- Responsive design

#### AppSidebar.vue
- Map layer toggles (Events, Zones, Equipment, Heatmap)
- Basemap selector
- Event type filters with color coding
- Date range filters
- Collapsible sections

### 6. Map Components

#### MapView.vue
- Main Leaflet map integration
- Event markers rendering
- Event popup with details
- Map state synchronization
- Layer visibility management

#### EventMarker.vue
- Customizable event marker
- Color-coded by event type
- Click handlers
- Hover effects

#### ControlZonePolygon.vue
- Territory control visualization
- Color-coded by controller
- Opacity based on control type
- Stroke patterns for contested/claimed areas

#### MapLegend.vue
- Dynamic legend generation
- Item visibility toggles
- Collapsible interface
- Color swatches with labels

#### DrawingTools.vue
- Polygon drawing
- Rectangle drawing
- Line drawing
- Marker placement
- Clear all function
- Integration with Leaflet.Draw

### 7. Event Components

#### EventList.vue
- Paginated event list
- Advanced filtering (type, confidence, status)
- Search functionality
- Sorting options
- Pagination controls

#### EventCard.vue
- Event summary display
- Color-coded event types
- Media thumbnails
- Verification badges
- Click to view details

#### EventForm.vue
- Create/edit event form
- Event type selection
- Date/time picker
- Location input (coordinates + name)
- Actor selector integration
- Source URL management
- Description editor
- Form validation

#### ActorSelector.vue
- Autocomplete search
- Fuzzy actor search
- Visual actor display with flags/logos
- Selected actor preview
- Debounced search

### 8. Timeline Component

#### TimelineBar.vue
- Date range selection
- Play/pause animation
- Playback speed control (0.5x to 10x)
- Step forward/backward controls
- Progress bar with slider
- Current date display
- Timeline reset function

### 9. View Pages

#### Dashboard.vue
- Statistics cards (total events, verified, conflicts, recent)
- Recent events list
- Quick action buttons
- Navigation to key features

#### MapPage.vue
- Full-screen map view
- Sidebar integration
- Drawing tools toggle
- Timeline toggle
- Map legend
- Responsive layout

#### EventDetail.vue
- Complete event information
- Media gallery
- Source links
- Actor and conflict details
- Location information
- Edit/delete actions (role-based)
- View on map button
- Metadata display

## Router Configuration

### Public Routes
- `/login` - Login page
- `/register` - Registration page

### Protected Routes (Requires Authentication)
- `/` - Dashboard
- `/map` - Interactive map
- `/events` - Events list
- `/events/:id` - Event detail
- `/equipment` - Equipment list
- `/equipment/:id` - Equipment detail
- `/zones` - Control zones list
- `/analytics` - Analytics dashboard
- `/settings` - User settings

### Admin Routes (Requires Admin Role)
- `/admin` - Admin dashboard
- `/admin/users` - User management
- `/admin/actors` - Actor management
- `/admin/conflicts` - Conflict management
- `/admin/audit-logs` - Audit logs

## Key Features

### Authentication & Authorization
- JWT token-based authentication
- Automatic token refresh
- Role-based access control
- Protected routes with guards
- Redirect to login on 401

### Map Features
- Multiple basemap options (OSM, Satellite, Terrain)
- Event markers with custom icons
- Control zone polygons
- Drawing tools for creating zones
- Legend with visibility toggles
- Popup information windows
- Map state persistence

### Event Management
- CRUD operations for events
- Advanced filtering and search
- Pagination
- Media attachment support
- Source URL management
- Confidence levels
- Verification workflow
- Actor attribution

### Timeline & Playback
- Historical event playback
- Variable speed controls
- Date range navigation
- Animation support

## Setup Instructions

### 1. Install Dependencies

```bash
cd /home/user/OsintWeb/resources/js
npm install
```

### 2. Development Server

```bash
npm run dev
```

### 3. Build for Production

```bash
npm run build
```

### 4. Type Checking

```bash
npm run type-check
```

## Configuration Files

### package.json
Contains all project dependencies and scripts. Copy from `package.json.example`.

### tsconfig.json
TypeScript compiler configuration with strict mode enabled and path aliases.

### vite.config.ts
Vite build configuration with Vue plugin and path resolution.

## Best Practices Followed

### TypeScript
- Strict mode enabled
- No `any` types (except where necessary)
- Interfaces for all data structures
- Type-safe API calls
- Proper type inference

### Vue 3 Composition API
- `<script setup>` syntax
- Reactive refs and computed properties
- Lifecycle hooks
- Proper component props typing
- Type-safe emits

### Code Organization
- Single responsibility principle
- Reusable composables
- Centralized state management
- Modular component structure
- Clear separation of concerns

### Performance
- Lazy loading for routes
- Computed properties for derived state
- Debounced search inputs
- Efficient list rendering
- Proper cleanup in composables

### Security
- CSRF token handling
- XSS prevention
- Secure authentication flow
- Input sanitization
- API error handling

## API Integration

All API calls go through the `useApi()` composable which handles:
- Base URL configuration (`/api`)
- Authentication headers
- Response transformation
- Error handling
- 401 redirect to login

Example usage:

```typescript
const api = useApi();

// GET request
const events = await api.get<PaginatedResponse<Event>>('/events');

// POST request
const newEvent = await api.post<Event>('/events', eventData);

// PUT request
const updated = await api.put<Event>(`/events/${id}`, eventData);

// DELETE request
await api.delete(`/events/${id}`);
```

## State Management

### Using Pinia Stores

```typescript
// In a component
import { useEventsStore } from '@/stores/events';

const eventsStore = useEventsStore();

// Access state
console.log(eventsStore.events);

// Call actions
await eventsStore.fetchEvents();
await eventsStore.createEvent(eventData);

// Use computed
const filteredEvents = eventsStore.filteredEvents;
```

## Map Integration

### Using the Map Composable

```typescript
import { useMap } from '@/composables/useMap';

const { initMap, addEventMarker, flyTo } = useMap(mapContainer, {
  center: [48.3794, 31.1656],
  zoom: 6
});

// Initialize map
const map = initMap();

// Add event marker
addEventMarker(event, handleEventClick);

// Fly to location
flyTo(lat, lng, 12);
```

## Development Notes

### Adding New Routes
1. Add route definition in `router/index.ts`
2. Create view component in `views/`
3. Update navigation in `AppHeader.vue` if needed

### Adding New Event Types
1. Update `EventType` in `types/index.ts`
2. Add color in event type color maps
3. Update event type labels in components
4. Update sidebar filters

### Adding New API Endpoints
1. Use `useApi()` composable
2. Add TypeScript types for request/response
3. Consider adding to appropriate Pinia store

## Notes

- All file paths use the `@/` alias which maps to `/home/user/OsintWeb/resources/js/`
- Components use Composition API with `<script setup lang="ts">`
- TailwindCSS classes are used for styling
- Leaflet CSS must be imported in `app.ts`
- Icons use inline SVGs for better control

## Next Steps

To complete the frontend implementation:

1. Set up Laravel backend API endpoints
2. Configure CORS for API access
3. Set up Vite in Laravel mix/asset pipeline
4. Create remaining view components referenced in router
5. Implement authentication pages (Login, Register)
6. Add unit and integration tests
7. Configure environment variables
8. Set up CI/CD pipeline
