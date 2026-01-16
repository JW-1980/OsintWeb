import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { Event, EventFilters, PaginatedResponse } from '@/types';
import { useApi } from '@/composables/useApi';

export const useEventsStore = defineStore('events', () => {
  const events = ref<Event[]>([]);
  const currentEvent = ref<Event | null>(null);
  const loading = ref(false);
  const filters = ref<EventFilters>({});
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 25,
    total: 0
  });

  const api = useApi();

  const filteredEvents = computed(() => {
    let result = events.value;

    if (filters.value.event_types?.length) {
      result = result.filter(e => filters.value.event_types?.includes(e.event_type));
    }

    if (filters.value.actor_ids?.length) {
      result = result.filter(e => e.actor_id && filters.value.actor_ids?.includes(e.actor_id));
    }

    if (filters.value.verified !== undefined) {
      result = result.filter(e => e.verified === filters.value.verified);
    }

    return result;
  });

  async function fetchEvents(page = 1): Promise<void> {
    loading.value = true;
    try {
      const params = new URLSearchParams({
        page: page.toString(),
        per_page: pagination.value.per_page.toString(),
        ...Object.fromEntries(
          Object.entries(filters.value).map(([k, v]) => [k, String(v)])
        )
      });

      const response = await api.get<PaginatedResponse<Event>>(`/events?${params}`);
      events.value = response.data;
      pagination.value = response.meta.pagination;
    } catch (error) {
      console.error('Failed to fetch events:', error);
      throw error;
    } finally {
      loading.value = false;
    }
  }

  async function fetchEvent(id: number | string): Promise<void> {
    loading.value = true;
    try {
      const response = await api.get<Event>(`/events/${id}`);
      currentEvent.value = response;
    } catch (error) {
      console.error('Failed to fetch event:', error);
      throw error;
    } finally {
      loading.value = false;
    }
  }

  async function createEvent(data: Partial<Event>): Promise<Event> {
    loading.value = true;
    try {
      const response = await api.post<Event>('/events', data);
      events.value.unshift(response);
      return response;
    } catch (error) {
      console.error('Failed to create event:', error);
      throw error;
    } finally {
      loading.value = false;
    }
  }

  async function updateEvent(id: number | string, data: Partial<Event>): Promise<Event> {
    loading.value = true;
    try {
      const response = await api.put<Event>(`/events/${id}`, data);
      const index = events.value.findIndex(e => e.id === response.id);
      if (index !== -1) {
        events.value[index] = response;
      }
      if (currentEvent.value?.id === response.id) {
        currentEvent.value = response;
      }
      return response;
    } catch (error) {
      console.error('Failed to update event:', error);
      throw error;
    } finally {
      loading.value = false;
    }
  }

  async function deleteEvent(id: number | string): Promise<void> {
    loading.value = true;
    try {
      await api.delete(`/events/${id}`);
      events.value = events.value.filter(e => e.id !== id);
      if (currentEvent.value?.id === id) {
        currentEvent.value = null;
      }
    } catch (error) {
      console.error('Failed to delete event:', error);
      throw error;
    } finally {
      loading.value = false;
    }
  }

  function setFilters(newFilters: EventFilters): void {
    filters.value = newFilters;
  }

  function clearFilters(): void {
    filters.value = {};
  }

  return {
    events,
    currentEvent,
    loading,
    filters,
    pagination,
    filteredEvents,
    fetchEvents,
    fetchEvent,
    createEvent,
    updateEvent,
    deleteEvent,
    setFilters,
    clearFilters
  };
});
